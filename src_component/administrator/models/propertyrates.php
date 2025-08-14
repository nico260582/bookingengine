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

        // 1. Get existing rates for the property
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . (int)$propertyId);
        $data->rates = $db->setQuery($query)->loadObjectList('season_name');

        // 2. Get seasons from the assigned supplier
        $query->clear()
            ->select('s.rules')
            ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
            ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
            ->where('m.property_id = ' . (int)$propertyId);
        $rulesJson = $db->setQuery($query)->loadResult();

        $supplierSeasons = [];
        if ($rulesJson) {
            $rules = json_decode($rulesJson);
            if (isset($rules->seasons) && is_string($rules->seasons)) {
                $supplierSeasons = json_decode($rules->seasons);
            } elseif (isset($rules->seasons) && (is_array($rules->seasons) || is_object($rules->seasons))) {
                $supplierSeasons = (array) $rules->seasons;
            }
        }

        // 3. Combine them
        $finalSeasons = [];
        $seasonNames = []; // To track unique season names

        // Add seasons from supplier first to maintain their order and dates
        foreach ($supplierSeasons as $season) {
            if (!in_array($season->name, $seasonNames)) {
                $finalSeasons[] = $season;
                $seasonNames[] = $season->name;
            }
        }

        // Add any seasons from existing rates that weren't in the supplier list
        if(is_array($data->rates)) {
            foreach ($data->rates as $rate) {
                if (!in_array($rate->season_name, $seasonNames)) {
                    $season = new stdClass();
                    $season->name = $rate->season_name;
                    $season->start_date = ''; // Dates are not available
                    $season->end_date = '';
                    $finalSeasons[] = $season;
                    $seasonNames[] = $rate->season_name;
                }
            }
        }


        $data->seasons = $finalSeasons;

        if (empty($data->seasons)) {
            $data->error = 'No rates or seasons found for this property. Please assign a supplier with defined seasons to begin.';
        }

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

        // Get existing rates to determine if we need to UPDATE or INSERT for each season
        $existingRatesQuery = $db->getQuery(true)
            ->select('season_name')
            ->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . $propertyId);
        $existingRates = $db->setQuery($existingRatesQuery)->loadColumn();
        $existingRates = array_flip($existingRates); // Flip for easy key checking

        // Loop through the submitted rates data from the form
        foreach ($ratesData as $seasonName => $seasonData) {
            // Sanitize season name just in case
            $seasonName = trim($seasonName);
            if (empty($seasonName)) {
                continue;
            }

            $rateObj = new stdClass();
            $rateObj->property_id = $propertyId;
            $rateObj->season_name = $seasonName;

            // Sanitize and validate base rate
            $baseRateInput = $seasonData['base_rate'] ?? '';
            $sanitizedRate = preg_replace('/[^\d\.]/', '', $baseRateInput);
            $rateObj->base_rate = ($sanitizedRate !== '' && is_numeric($sanitizedRate)) ? (float)$sanitizedRate : null;

            // Handle commission override
            $rateObj->override_admin_commission = (isset($seasonData['override_admin_commission']) && $seasonData['override_admin_commission'] == '1') ? 1 : 0;

            if ($rateObj->override_admin_commission) {
                $commissionInput = $seasonData['admin_commission'] ?? '';
                $rateObj->admin_commission = is_numeric($commissionInput) ? (float)$commissionInput : null;
            } else {
                $rateObj->admin_commission = null;
            }

            // We should only save a rate if it contains some data.
            // An empty row in the form should not result in a database entry unless it exists.
            $hasData = $rateObj->base_rate !== null || $rateObj->override_admin_commission == 1 || ($rateObj->override_admin_commission == 1 && $rateObj->admin_commission !== null);

            if (isset($existingRates[$seasonName])) {
                // This season already has a rate record, so we UPDATE it.
                // Even if the user cleared the fields, we update with nulls.
                $db->updateObject('#__bookingmanager_rates', $rateObj, ['property_id', 'season_name']);
            } else {
                // This is a new season for this property.
                // Only INSERT if the user has actually entered some data.
                if ($hasData) {
                   $db->insertObject('#__bookingmanager_rates', $rateObj);
                }
            }
        }

        return true;
    }
}