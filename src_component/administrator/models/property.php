<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

class BookingmanagerModelProperty extends AdminModel
{
    public function getTable($type = 'Property', $prefix = 'BookingmanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        Form::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');

        $form = $this->loadForm(
            'com_bookingmanager.property',
            'property',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form)) {
            return false;
        }

        $item = $this->getItem();

        if ($item && !empty($item->id)) {
            $form->setFieldAttribute('article_id', 'type', 'text');
            $form->setFieldAttribute('article_id', 'readonly', 'true');
            $form->setFieldAttribute('article_id', 'class', 'readonly');

            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('id') . ' = ' . (int)$item->article_id);
            $articleTitle = $db->setQuery($query)->loadResult();

            $form->setValue('article_id', null, $articleTitle);
        }

        return $form;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && !empty($item->id) && !isset($item->ratesData)) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            if (empty($item->article_id)) {
                $query->select($db->quoteName('article_id'))
                    ->from($db->quoteName('#__bookingmanager_properties'))
                    ->where($db->quoteName('id') . ' = ' . (int) $item->id);
                $item->article_id = $db->setQuery($query)->loadResult();
            }

            $query->clear()
                ->select('complex_id, priority')
                ->from('#__bookingmanager_complex_property_map')
                ->where('property_id = ' . (int) $item->id);
            $item->complexes = $db->setQuery($query)->loadObjectList('complex_id');

            if (!empty($item->article_id)) {
                $ratesData = new stdClass();
                $propertyId = (int) $item->article_id;

                $query->clear()
                    ->select('s.rules')
                    ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
                    ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
                    ->where('m.property_id = ' . $propertyId);
                $rulesJson = $db->setQuery($query)->loadResult();

                if (empty($rulesJson)) {
                    $ratesData->error = 'This property is not assigned to a supplier.';
                } else {
                    $rules = json_decode($rulesJson);
                    $seasons = [];
                    if (isset($rules->seasons)) {
                        $seasons = array_values((array) $rules->seasons);
                    }
                    $ratesData->seasons = $seasons;

                    if (empty($ratesData->seasons)) {
                        $ratesData->error = 'The assigned supplier has no seasons defined.';
                    } else {
                        // Get supplier markets
                        $query->clear()
                            ->select('s.id')
                            ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
                            ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
                            ->where('m.property_id = ' . $propertyId);
                        $supplierId = $db->setQuery($query)->loadResult();

                        $query->clear()
                            ->select('market_name, currency')
                            ->from('#__bookingmanager_supplier_markets')
                            ->where('supplier_id = ' . (int)$supplierId)
                            ->order('id ASC');
                        $markets = $db->setQuery($query)->loadObjectList();

                        // Always add a "Default" market for the global rate
                        $defaultMarket = (object)['market_name' => 'Default', 'currency' => 'EUR'];
                        array_unshift($markets, $defaultMarket);
                        $ratesData->markets = $markets;

                        // Get all saved rates for this property
                        $query->clear()
                            ->select('season_name, rates')
                            ->from($db->quoteName('#__bookingmanager_rates'))
                            ->where('property_id = ' . (int) $propertyId);
                        $ratesList = $db->setQuery($query)->loadObjectList('season_name');

                        // Decode the JSON for each season's rates
                        foreach ($ratesList as $seasonName => $rate) {
                            if (!empty($rate->rates)) {
                                $ratesList[$seasonName]->rates = json_decode($rate->rates, true);
                            } else {
                                $ratesList[$seasonName]->rates = [];
                            }
                        }
                        $ratesData->rates = $ratesList;
                    }
                }
                $item->ratesData = $ratesData;
            } else {
                $item->ratesData = new stdClass();
                $item->ratesData->error = 'This property is not linked to a Joomla Article.';
            }
        } elseif (!$item) {
            $item = $this->getTable();
            $item->id = 0;
        }

        return $item;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.property.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        if (!empty($data['id'])) {
            $table = $this->getTable();
            $table->load($data['id']);
            $data['article_id'] = $table->article_id;
        }

        if (!parent::save($data)) {
            return false;
        }

        $propertyId = (int)$this->getState($this->getName() . '.id');
        $db = Factory::getDbo();

        // Handle complex assignments with priority re-ordering
        $complexes = $data['complexes'] ?? [];

        // 1. Filter for assigned complexes and store their user-defined priorities
        $assignedComplexes = [];
        foreach ($complexes as $complexId => $complexData) {
            if (!empty($complexData['assign'])) {
                $assignedComplexes[] = [
                    'complex_id' => (int)$complexId,
                    'priority'   => (int)($complexData['priority'] ?? 0)
                ];
            }
        }

        // 2. Sort the assigned complexes
        usort($assignedComplexes, function ($a, $b) {
            $priorityA = $a['priority'];
            $priorityB = $b['priority'];

            // Treat 0 as a high number to push it to the end of user-prioritized items
            if ($priorityA === 0) $priorityA = 9999;
            if ($priorityB === 0) $priorityB = 9999;

            if ($priorityA == $priorityB) {
                // If priorities are the same, maintain original order (or sort by id for stability)
                return $a['complex_id'] - $b['complex_id'];
            }
            return ($priorityA < $priorityB) ? -1 : 1;
        });

        // 3. Delete old assignments
        $query = $db->getQuery(true)
            ->delete('#__bookingmanager_complex_property_map')
            ->where('property_id = ' . $propertyId);
        $db->setQuery($query)->execute();

        // 4. Insert new assignments with re-calculated sequential priorities
        $newPriority = 1;
        foreach ($assignedComplexes as $assignment) {
            $map = new stdClass();
            $map->property_id = $propertyId;
            $map->complex_id = $assignment['complex_id'];
            $map->priority = $newPriority++;
            $db->insertObject('#__bookingmanager_complex_property_map', $map);
        }

        if (isset($data['rates'])) {
            AdminModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
            $ratesModel = AdminModel::getInstance('Propertyrates', 'BookingmanagerModel');

            if ($ratesModel) {
                $table = $this->getTable();
                $table->load($propertyId);
                $articleId = $table->article_id;

                $ratesData = [
                    'property_id' => $articleId,
                    'rates' => $data['rates']
                ];

                if (!$ratesModel->save($ratesData)) {
                    $this->setError($ratesModel->getError());
                    return false;
                }
            }
        }

        return true;
    }

    public function delete(&$pks)
    {
        $db = $this->getDbo();
        foreach ($pks as $pk) {
            $table = $this->getTable();
            $table->load($pk);
            $articleId = $table->article_id;

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__bookingmanager_complex_property_map'))
                ->where('property_id = ' . (int)$pk);
            $db->setQuery($query)->execute();

            if ($articleId) {
                $query->clear()
                    ->delete($db->quoteName('#__bookingmanager_rates'))
                    ->where('property_id = ' . (int)$articleId);
                $db->setQuery($query)->execute();
            }
        }

        return parent::delete($pks);
    }

    public function publish(&$pks, $value = 1)
    {
        $pks = (array) $pks;

        // Validation only runs on publish, not unpublish
        if ($value == 1) {
            AdminModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
            $ratesModel = AdminModel::getInstance('Propertyrates', 'BookingmanagerModel');

            if (!$ratesModel) {
                $this->setError('Could not load rates model.');
                return false;
            }

            foreach ($pks as $pk) {
                $table = $this->getTable();
                $table->load($pk);
                $articleId = $table->article_id;

                if (!$articleId) {
                    $this->setError('Property ID ' . $pk . ' is not linked to an article and cannot be published.');
                    return false;
                }

                $ratesData = $ratesModel->getRateData($articleId);

                if (empty($ratesData) || isset($ratesData->error) || empty($ratesData->seasons)) {
                    $this->setError('Property ID ' . $pk . ' cannot be published. It may not be assigned to a supplier with seasons defined.');
                    return false;
                }

                foreach ($ratesData->seasons as $season) {
                    $seasonName = $season->name;
                    $seasonRates = $ratesData->rates[$seasonName]->rates ?? [];
                    $defaultRateInfo = $seasonRates['Default'] ?? [];

                    if (empty($defaultRateInfo) || !isset($defaultRateInfo['rate']) || $defaultRateInfo['rate'] === '') {
                        $this->setError('Property ID ' . $pk . ' cannot be published. Please fill in the rate for the "Default" market for all seasons first.');
                        return false;
                    }
                }
            }
        }

        // If validation passes (or we are unpublishing), publish the associated Joomla articles.
        try {
            foreach ($pks as $pk) {
                $table = $this->getTable();
                $table->load($pk);
                $articleId = $table->article_id;

                if ($articleId) {
                    $articleTable = JTable::getInstance('Content', 'JTable');
                    $articleTable->load($articleId);

                    if ($articleTable->state != $value) {
                        $articleTable->state = $value;
                        if (!$articleTable->store()) {
                            $this->setError($articleTable->getError());
                            return false;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }
}
