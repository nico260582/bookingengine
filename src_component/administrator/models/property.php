<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;

class BookingmanagerModelProperty extends AdminModel
{
    public function getTable($type = 'Property', $prefix = 'BookingmanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
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

        // If we are editing an existing item, make the article field readonly
        // and display the article title instead of a dropdown.
        if ($item && !empty($item->id)) {
            $form->setFieldAttribute('article_id', 'type', 'text');
            $form->setFieldAttribute('article_id', 'readonly', 'true');
            $form->setFieldAttribute('article_id', 'class', 'readonly'); // For styling

            // We need to get the article title to display it
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('id') . ' = ' . (int)$item->article_id);
            $articleTitle = $db->setQuery($query)->loadResult();

            // Set the value of the field to the article title
            $form->setValue('article_id', null, $articleTitle);
        }

        return $form;
    }

public function getItem($pk = null)
{
    // Get the item, either from the state or from the parent method
    $item = parent::getItem($pk);

    if ($item && !empty($item->id) && !isset($item->ratesData)) {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // 1. Get the article_id if it's not already present
        if (empty($item->article_id)) {
            $query->select($db->quoteName('article_id'))
                ->from($db->quoteName('#__bookingmanager_properties'))
                ->where($db->quoteName('id') . ' = ' . (int) $item->id);
            $item->article_id = $db->setQuery($query)->loadResult();
        }

        // 2. Load the complex ID
        $query->clear()
            ->select($db->quoteName('complex_id'))
            ->from($db->quoteName('#__bookingmanager_complex_property_map'))
            ->where($db->quoteName('property_id') . ' = ' . (int) $item->id);
        $item->complex_id = $db->setQuery($query)->loadResult();

        // 3. Load the rates data directly here
        if (!empty($item->article_id)) {
            $ratesData = new stdClass();
            $propertyId = (int) $item->article_id;

            // Get supplier rules
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
                    // Get the saved rates
                    $query->clear()
                        ->select('*')
                        ->from($db->quoteName('#__bookingmanager_rates'))
                        ->where($db->quoteName('property_id') . ' = ' . $propertyId);
                    $ratesList = $db->setQuery($query)->loadObjectList('season_name');
                    $ratesData->rates = $ratesList;
                }
            }
            $item->ratesData = $ratesData;
        } else {
            $item->ratesData = new stdClass();
            $item->ratesData->error = 'This property is not linked to a Joomla Article.';
        }
    } elseif (!$item) {
        // For a new item, return a table object with default values to prevent errors
        $item = $this->getTable();
        $item->id = 0;
    }

    return $item;
}

    protected function loadFormData()
    {
        // Load the data from the session.
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.property.data', array());

        if (empty($data)) {
            // If no session data, load from the database.
            // The getItem method will now handle loading all related data.
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        // If we are editing an existing property, the form submits the article TITLE instead of the ID.
        // To prevent an SQL error, we load the record and overwrite the submitted article_id
        // with the correct one from the database. This also enforces that the article cannot be changed on edit.
        if (!empty($data['id'])) {
            $table = $this->getTable();
            $table->load($data['id']);
            $data['article_id'] = $table->article_id;
        }

        // Save the main property data using the parent AdminModel's save
        if (!parent::save($data)) {
            return false;
        }

        // Get the ID of the saved property
        $propertyId = (int)$this->getState($this->getName() . '.id');

        // --- Complex Mapping Logic ---
        $complexId  = $data['complex_id'] ?? 0;
        $db = Factory::getDbo();

        // First, remove existing mapping for this property
        $query = $db->getQuery(true)
            ->delete('#__bookingmanager_complex_property_map')
            ->where('property_id = ' . $propertyId);
        $db->setQuery($query)->execute();

        // If a complex was selected, add the new mapping
        if (!empty($complexId)) {
            $map = new stdClass();
            $map->property_id = $propertyId;
            $map->complex_id = (int)$complexId;
            $db->insertObject('#__bookingmanager_complex_property_map', $map);
        }

        // --- Rates Saving Logic ---
        if (isset($data['rates'])) {
            AdminModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
            $ratesModel = AdminModel::getInstance('Propertyrates', 'BookingmanagerModel');

            if ($ratesModel) {
                // Get the article_id for the property
                $table = $this->getTable();
                $table->load($propertyId);
                $articleId = $table->article_id;

                $ratesData = [
                    'property_id' => $articleId, // Use the article_id
                    'rates' => $data['rates']
                ];

                // --- DIAGNOSTIC STEP ---
                // Display the article ID to verify it's correct before saving rates.
                $this->setError("DIAGNOSTIC: The Article ID being used to save rates is: " . (int) $articleId);
                return false; // Stop execution so the user can see the message.

                /*
                if (!$ratesModel->save($ratesData)) {
                    $this->setError($ratesModel->getError());
                    return false;
                }
                */
            }
        }

        return true;
    }

    public function delete(&$pks)
    {
        $db = $this->getDbo();
        foreach ($pks as $pk) {
            // Get the article_id for this property before deleting
            $table = $this->getTable();
            $table->load($pk);
            $articleId = $table->article_id;

            // Delete from complex map table
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__bookingmanager_complex_property_map'))
                ->where('property_id = ' . (int)$pk);
            $db->setQuery($query)->execute();

            // Delete from rates table using the article_id
            if ($articleId) {
                $query->clear()
                    ->delete($db->quoteName('#__bookingmanager_rates'))
                    ->where('property_id = ' . (int)$articleId);
                $db->setQuery($query)->execute();
            }
        }

        // Call parent delete to remove from main properties table
        return parent::delete($pks);
    }

    public function publish(&$pks, $value = 1)
    {
        $pks = (array) $pks;
        $db = $this->getDbo();

        // Validation: Only check if we are trying to publish
        if ($value == 1) {
            // Get the rates model to reuse its logic
            AdminModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
            $ratesModel = AdminModel::getInstance('Propertyrates', 'BookingmanagerModel');

            if (!$ratesModel) {
                $this->setError('Could not load rates model.');
                return false;
            }

            foreach ($pks as $pk) {
                $ratesData = $ratesModel->getRateData($pk);
                if (empty($ratesData) || isset($ratesData->error) || empty($ratesData->seasons)) {
                    $this->setError('Property ID ' . $pk . ' cannot be published. It may not be assigned to a supplier with seasons defined.');
                    return false;
                }

                foreach ($ratesData->seasons as $season) {
                    $seasonName = $season->name;
                    if (!isset($ratesData->rates[$seasonName]) || !isset($ratesData->rates[$seasonName]->base_rate) || $ratesData->rates[$seasonName]->base_rate === '') {
                        $this->setError('Property ID ' . $pk . ' cannot be published. Please fill in the base rate for all seasons first.');
                        return false;
                    }
                }
            }
        }

        // If validation passes or we are unpublishing, proceed.
        return parent::publish($pks, $value);
    }
}
