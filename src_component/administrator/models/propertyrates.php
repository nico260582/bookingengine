<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class BookingmanagerModelPropertyrates extends BaseDatabaseModel
{
    public function getPropertiesForFilter()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)->select('c.id, c.title')->from($db->quoteName('#__content', 'c'))
            ->join('INNER', $db->quoteName('#__bookingmanager_property_map', 'm') . ' ON c.id = m.property_id')
            ->where('c.state = 1')->order('c.title');
        return $db->setQuery($query)->loadObjectList();
    }

    public function getRateData($propertyId)
    {
        if (!$propertyId) { return null; }
        $db = $this->getDbo();
        $data = new stdClass();
        
        $query = $db->getQuery(true)->select('s.rules')->from($db->quoteName('#__bookingmanager_property_map', 'm'))
            ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
            ->where('m.property_id = ' . (int)$propertyId);
        $rulesJson = $db->setQuery($query)->loadResult();
        
        if (empty($rulesJson)) {
            $data->error = 'This property is not assigned to a supplier with defined seasons.';
            return $data;
        }
        
        $seasons = [];
        if ($rulesJson) {
            $rules = json_decode($rulesJson);
            if (isset($rules->seasons) && is_string($rules->seasons)) {
                $seasons = json_decode($rules->seasons);
            } elseif (isset($rules->seasons) && (is_array($rules->seasons) || is_object($rules->seasons))) {
                $seasons = (array) $rules->seasons;
            }
        }
        $data->seasons = $seasons;
        
        $query->clear()->select('*')->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . (int)$propertyId);
        $data->rates = $db->setQuery($query)->loadObjectList('season_name');
        
        return $data;
    }

    public function save($data)
    {
        $propertyId = (int)($data['property_id'] ?? 0);
        $ratesData = $data['rates'] ?? [];

        if (!$propertyId) {
            $this->setError('No property selected.');
            return false;
        }

        $db = $this->getDbo();

        // First, get the seasons defined for this property's supplier
        $subQuery = $db->getQuery(true)->select('s.rules')->from($db->quoteName('#__bookingmanager_property_map', 'm'))
            ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
            ->where('m.property_id = ' . (int)$propertyId);
        $rulesJson = $db->setQuery($subQuery)->loadResult();

        $seasons = [];
        if ($rulesJson) {
            $rules = json_decode($rulesJson);
            if (isset($rules->seasons) && is_string($rules->seasons)) {
                $seasons = json_decode($rules->seasons);
            } elseif (isset($rules->seasons) && (is_array($rules->seasons) || is_object($rules->seasons))) {
                $seasons = (array) $rules->seasons;
            }
        }

        // Delete all existing rates for the property before inserting new ones
        $query = $db->getQuery(true)->delete($db->quoteName('#__bookingmanager_rates'))->where('property_id = ' . $propertyId);
        $db->setQuery($query)->execute();

        // Loop through all available seasons, not just the submitted data
        foreach ($seasons as $season) {
            $seasonName = $season->name;
            $seasonData = $ratesData[$seasonName] ?? [];

            $rateObj = new stdClass();
            $rateObj->property_id = $propertyId;
            $rateObj->season_name = $seasonName;

            // Sanitize and set base_rate, allowing it to be null
            $baseRateInput = $seasonData['base_rate'] ?? '';
            $sanitizedRate = preg_replace('/[^\d\.]/', '', $baseRateInput);
            $rateObj->base_rate = ($sanitizedRate !== '' && is_numeric($sanitizedRate)) ? (float)$sanitizedRate : null;

            // Handle override commission checkbox
            $rateObj->override_admin_commission = (isset($seasonData['override_admin_commission']) && $seasonData['override_admin_commission'] == '1') ? 1 : 0;

            // Handle admin commission value, only if override is checked
            if ($rateObj->override_admin_commission) {
                $commissionInput = $seasonData['admin_commission'] ?? '';
                $rateObj->admin_commission = is_numeric($commissionInput) ? (float)$commissionInput : null;
            } else {
                $rateObj->admin_commission = null;
            }

            $db->insertObject('#__bookingmanager_rates', $rateObj);
        }

        return true;
    }
}