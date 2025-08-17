<?php
    defined('_JEXEC') or die;

    use Joomla\CMS\MVC\Model\BaseDatabaseModel;
    use Joomla\CMS\Factory;

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
            if (!$propertyId) {
                return null;
            }
            $db = $this->getDbo();
            $data = new stdClass();

            $query = $db->getQuery(true)
                ->select('s.id as supplier_id, s.rules')
                ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
                ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
                ->where('m.property_id = ' . (int) $propertyId);

            $supplierInfo = $db->setQuery($query)->loadObject();

            if (empty($supplierInfo) || empty($supplierInfo->rules)) {
                $data->error = 'This property (Article ID: ' . $propertyId . ') is not assigned to a supplier with defined seasons.';
                return $data;
            }

            // Get supplier markets with currencies
            $query->clear()
                ->select('market_name, currency')
                ->from('#__bookingmanager_supplier_markets')
                ->where('supplier_id = ' . (int)$supplierInfo->supplier_id)
                ->order('id ASC');
            $data->markets = $db->setQuery($query)->loadObjectList();

            if (empty($data->markets)) {
                $data->markets = [(object)['market_name' => 'Default', 'currency' => 'EUR']];
            }

            $rules = json_decode($supplierInfo->rules);
            $seasons = [];
            if (isset($rules->seasons)) {
                $seasons = array_values((array) $rules->seasons);
            }
            $data->seasons = $seasons;

            if (empty($data->seasons)) {
                $data->error = 'The assigned supplier does not have any seasons defined.';
                return $data;
            }

            $query->clear()
                ->select('season_name, rates')
                ->from($db->quoteName('#__bookingmanager_rates'))
                ->where('property_id = ' . (int) $propertyId);

            $ratesList = $db->setQuery($query)->loadObjectList('season_name');

            foreach ($ratesList as $seasonName => $rate) {
                $ratesList[$seasonName]->rates = json_decode($rate->rates, true);
            }
            $data->rates = $ratesList;

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

            foreach ($ratesData as $seasonName => $seasonRates) {
                $rateObj = new stdClass();
                $rateObj->property_id = $propertyId;
                $rateObj->season_name = $seasonName;
                $rateObj->rates = json_encode($seasonRates);

                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__bookingmanager_rates'))
                    ->where('property_id = ' . $propertyId)
                    ->where('season_name = ' . $db->quote($seasonName));
                $exists = $db->setQuery($query)->loadResult();

                if ($exists) {
                    if (!$db->updateObject('#__bookingmanager_rates', $rateObj, ['property_id', 'season_name'])) {
                        $this->setError($db->getErrorMsg());
                        return false;
                    }
                } else {
                    if (!$db->insertObject('#__bookingmanager_rates', $rateObj)) {
                        $this->setError($db->getErrorMsg());
                        return false;
                    }
                }
            }

            return true;
        }
    }