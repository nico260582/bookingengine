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

        // Always set the debug JSON
        $data->debug_rules_json = $rulesJson;

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
        $data->debug_seasons_count = count($seasons);

        if (empty($rulesJson)) {
            $data->error = 'This property (Article ID: ' . $propertyId . ') is not assigned to a supplier with defined seasons.';
            return $data;
        }
        
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

        // Get the seasons defined for this property's supplier
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

        // Get existing rates to determine if we need to UPDATE or INSERT
        $existingRatesQuery = $db->getQuery(true)->select('season_name')->from($db->quoteName('#__bookingmanager_rates'))->where('property_id = ' . $propertyId);
        $existingRates = $db->setQuery($existingRatesQuery)->loadColumn();
        $existingRates = array_flip($existingRates);

        // Loop through all available seasons
        foreach ($seasons as $season) {
            $seasonName = $season->name;
            $seasonData = $ratesData[$seasonName] ?? [];

            $rateObj = new stdClass();
            $rateObj->property_id = $propertyId;
            $rateObj->season_name = $seasonName;

            $baseRateInput = $seasonData['base_rate'] ?? '';
            $sanitizedRate = preg_replace('/[^\d\.]/', '', $baseRateInput);
            $rateObj->base_rate = ($sanitizedRate !== '' && is_numeric($sanitizedRate)) ? (float)$sanitizedRate : null;

            $rateObj->override_admin_commission = (isset($seasonData['override_admin_commission']) && $seasonData['override_admin_commission'] == '1') ? 1 : 0;

            if ($rateObj->override_admin_commission) {
                $commissionInput = $seasonData['admin_commission'] ?? '';
                $rateObj->admin_commission = is_numeric($commissionInput) ? (float)$commissionInput : null;
            } else {
                $rateObj->admin_commission = null;
            }

            // Decide whether to UPDATE or INSERT
            if (isset($existingRates[$seasonName])) {
                $db->updateObject('#__bookingmanager_rates', $rateObj, ['property_id', 'season_name']);
            } else {
                // We must insert a new row only if there is data for it.
                // The original logic was to only insert if base_rate was present.
                // Now, we insert if any value is present.
                if ($rateObj->base_rate !== null || $rateObj->override_admin_commission) {
                   $db->insertObject('#__bookingmanager_rates', $rateObj);
                }
            }
        }

        return true;
    }
}