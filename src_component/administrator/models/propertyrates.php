<?php
    defined('_JEXEC') or die;

    use Joomla\CMS\MVC\Model\BaseDatabaseModel;
    use Joomla\CMS\Factory;

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

            // Get supplier markets
            $query->clear()
                ->select('market_name, currency')
                ->from('#__bookingmanager_supplier_markets')
                ->where('supplier_id = ' . (int)$supplierInfo->supplier_id)
                ->where('state = 1')
                ->order('id ASC');
            $markets = $db->setQuery($query)->loadObjectList();

            // Always add a "Global Rate" market
            $defaultMarket = (object)['market_name' => 'Global Rate', 'currency' => 'EUR'];
            array_unshift($markets, $defaultMarket);
            $data->markets = $markets;

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

            // Get all saved rates for this property
            $query->clear()
                ->select('season_name, rates, active_markets')
                ->from($db->quoteName('#__bookingmanager_rates'))
                ->where('property_id = ' . (int) $propertyId);
            $ratesList = $db->setQuery($query)->loadObjectList('season_name');

            $data->active_markets = [];
            foreach ($ratesList as $seasonName => $rate) {
                if (!empty($rate->rates)) {
                    $ratesList[$seasonName]->rates = json_decode($rate->rates, true);
                } else {
                    $ratesList[$seasonName]->rates = [];
                }
                // Load active markets from the first available season
                if (empty($data->active_markets) && !empty($rate->active_markets)) {
                    $data->active_markets = json_decode($rate->active_markets, true);
                }
            }
            $data->rates = $ratesList;

            return $data;
        }

        public function save($data)
        {
            $propertyId = (int)($data['property_id'] ?? 0);
            $ratesData = $data['rates'] ?? [];
            $activeMarkets = $data['active_markets'] ?? [];
            $activeMarketsJson = json_encode(array_keys($activeMarkets));

            if (!$propertyId) {
                $this->setError('No property selected.');
                return false;
            }

            $db = $this->getDbo();
            $query = $db->getQuery(true);

            foreach ($ratesData as $seasonName => $submittedSeasonRates) {

                // 1. Load existing rates for this season to preserve data for inactive markets
                $query->clear()
                    ->select('rates')
                    ->from($db->quoteName('#__bookingmanager_rates'))
                    ->where($db->quoteName('property_id') . ' = ' . $propertyId)
                    ->where($db->quoteName('season_name') . ' = ' . $db->quote($seasonName));
                $existingRatesJson = $db->setQuery($query)->loadResult();
                $existingRates = $existingRatesJson ? json_decode($existingRatesJson, true) : [];
                if (!is_array($existingRates)) {
                    $existingRates = [];
                }

                // For any market that was submitted, we should reset its 'override_commission'
                // in the existing data before merging. This ensures that if the checkbox was
                // unchecked (and thus not in the submission), the old value is cleared.
                foreach (array_keys($submittedSeasonRates) as $marketName) {
                    if (isset($existingRates[$marketName]['override_commission'])) {
                        unset($existingRates[$marketName]['override_commission']);
                    }
                    if (isset($existingRates[$marketName]['commission'])) {
                        unset($existingRates[$marketName]['commission']);
                    }
                }

                // 2. Merge new data into existing data
                $mergedRates = array_replace_recursive($existingRates, $submittedSeasonRates);

                // 3. Sanitize and structure the merged data
                $sanitizedMarketData = [];
                foreach ($mergedRates as $marketName => $marketData) {
                    $sanitizedMarketData[$marketName] = [
                        'rate' => isset($marketData['rate']) && is_numeric($marketData['rate']) ? (float)$marketData['rate'] : null,
                        'override_commission' => isset($marketData['override_commission']) ? 1 : 0,
                        'commission' => isset($marketData['commission']) && is_numeric($marketData['commission']) ? (float)$marketData['commission'] : null,
                    ];
                }
                $ratesJson = json_encode($sanitizedMarketData);

                // 4. Check if a rate row for this season already exists
                $query->clear()
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__bookingmanager_rates'))
                    ->where($db->quoteName('property_id') . ' = ' . $propertyId)
                    ->where($db->quoteName('season_name') . ' = ' . $db->quote($seasonName));
                $exists = $db->setQuery($query)->loadResult();

                try {
                    if ($exists) {
                        // Build UPDATE query
                        $query->clear()
                            ->update($db->quoteName('#__bookingmanager_rates'))
                            ->set($db->quoteName('rates') . ' = ' . $db->quote($ratesJson))
                            ->set($db->quoteName('active_markets') . ' = ' . $db->quote($activeMarketsJson))
                            ->where($db->quoteName('property_id') . ' = ' . $propertyId)
                            ->where($db->quoteName('season_name') . ' = ' . $db->quote($seasonName));
                    } else {
                        // Build INSERT query
                        $columns = ['property_id', 'season_name', 'rates', 'active_markets'];
                        $values = [$propertyId, $db->quote($seasonName), $db->quote($ratesJson), $db->quote($activeMarketsJson)];
                        $query->clear()
                            ->insert($db->quoteName('#__bookingmanager_rates'))
                            ->columns($columns)
                            ->values(implode(',', $values));
                    }
                    $db->setQuery($query)->execute();
                } catch (\Exception $e) {
                    $this->setError($e->getMessage());
                    return false;
                }
            }

            return true;
        }
    }