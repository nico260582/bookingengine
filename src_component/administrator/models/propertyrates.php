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
        
        $rules = json_decode($rulesJson);
        $data->seasons = $rules->seasons ?? [];
        
        $query->clear()->select('season_name, base_rate')->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . (int)$propertyId);
        $data->rates = $db->setQuery($query)->loadObjectList('season_name');
        
        return $data;
    }

    public function save($data)
    {
        $propertyId = (int)($data['property_id'] ?? 0);
        $rates = $data['rates'] ?? [];
        if (!$propertyId) {
            $this->setError('No property selected.');
            return false;
        }
        $db = $this->getDbo();
        $query = $db->getQuery(true)->delete($db->quoteName('#__bookingmanager_rates'))->where('property_id = ' . $propertyId);
        $db->setQuery($query)->execute();

        foreach ($rates as $seasonName => $baseRate) {
            if ($baseRate !== '' && is_numeric($baseRate)) {
                $rateObj = new stdClass();
                $rateObj->property_id = $propertyId;
                $rateObj->season_name = $seasonName;
                $rateObj->base_rate = (float)$baseRate;
                $db->insertObject('#__bookingmanager_rates', $rateObj);
            }
        }
        return true;
    }
}