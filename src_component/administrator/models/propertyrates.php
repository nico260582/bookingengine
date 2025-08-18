<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class BookingmanagerModelPropertyrates extends BaseDatabaseModel
{
        public function getForm($data = array(), $loadData = true)
        {
            $form = $this->loadForm('com_bookingmanager.propertyrates', 'propertyrates', ['control' => 'jform', 'load_data' => $loadData]);
            if (empty($form)) {
                return false;
            }
            return $form;
        }

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
        if (!$propertyId) {
            return null;
        }
        $db = $this->getDbo();
        $data = new stdClass();

        // This query correctly uses the property_id (which is the article_id) from the map table
        $query = $db->getQuery(true)
            ->select('s.rules')
            ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
            ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
            ->where('m.property_id = ' . (int) $propertyId);

        $rulesJson = $db->setQuery($query)->loadResult();

        if (empty($rulesJson)) {
            $data->error = 'This property (Article ID: ' . $propertyId . ') is not assigned to a supplier. Please assign it to a supplier to manage rates.';
            return $data;
        }

        $rules = json_decode($rulesJson);
        $seasons = [];

        if (isset($rules->seasons)) {
            if (is_string($rules->seasons)) {
                // Handles the case where seasons are a JSON string within the JSON
                $seasons = json_decode($rules->seasons);
            } elseif (is_object($rules->seasons) || is_array($rules->seasons)) {
                // Handles the case where seasons are an object from a subform field
                $seasons = array_values((array) $rules->seasons);
            }
        }

        $data->seasons = $seasons;

        if (empty($data->seasons)) {
            $data->error = 'The assigned supplier does not have any seasons defined. Please define seasons for the supplier first.';
            return $data;
        }

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . (int) $propertyId);

        $data->rates = $db->setQuery($query)->loadObjectList('season_name');

        return $data;
    }

    public function save($data)
    {
            $log_file = JPATH_ROOT . '/jules_rates_debug_log.txt';
            file_put_contents($log_file, "--- SAVE ---\n", FILE_APPEND);
            file_put_contents($log_file, 'Timestamp: ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
            file_put_contents($log_file, 'Received data: ' . print_r($data, true) . "\n\n", FILE_APPEND);

        $propertyId = (int)($data['property_id'] ?? 0); // This is the Article ID
        $ratesData = $data['rates'] ?? [];

        if (!$propertyId) {
            $this->setError('No property selected.');
            return false;
        }

        $db = $this->getDbo();

        $existingRatesQuery = $db->getQuery(true)
            ->select('season_name')
            ->from($db->quoteName('#__bookingmanager_rates'))
            ->where('property_id = ' . $propertyId);
        $existingRates = $db->setQuery($existingRatesQuery)->loadColumn();
        $existingRates = array_flip($existingRates);

        foreach ($ratesData as $seasonName => $seasonData) {
            $seasonName = trim($seasonName);
            if (empty($seasonName)) {
                continue;
            }

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

            $hasData = $rateObj->base_rate !== null || $rateObj->override_admin_commission == 1;

            if (isset($existingRates[$seasonName])) {
                if (!$db->updateObject('#__bookingmanager_rates', $rateObj, ['property_id', 'season_name'])) {
                    $this->setError($db->getErrorMsg());
                    return false;
                }
            } else {
                if ($hasData) {
                    if (!$db->insertObject('#__bookingmanager_rates', $rateObj)) {
                        $this->setError($db->getErrorMsg());
                        return false;
                    }
                }
            }
        }

        return true;
    }
}