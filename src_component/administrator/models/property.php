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

        // If we are editing an existing item and it is already linked to an article,
        // make the article field readonly and display the article title.
        if ($item && !empty($item->id) && !empty($item->article_id)) {
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

    protected function loadFormData()
    {
        // Load the data from the session.
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.property.data', array());

        if (empty($data)) {
            // If no session data, load from the database
            $data = $this->getItem();
        }

        // Always load the related data for an existing item
        if ($data && !empty($data->id)) {
            // Load the complex ID from the mapping table
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('complex_id'))
                ->from($db->quoteName('#__bookingmanager_complex_property_map'))
                ->where($db->quoteName('property_id') . ' = ' . (int)$data->id);
            $data->complex_id = $db->setQuery($query)->loadResult();

            // Load the rates data
            AdminModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
            $ratesModel = AdminModel::getInstance('Propertyrates', 'BookingmanagerModel');
            if ($ratesModel) {
                $data->ratesData = $ratesModel->getRateData($data->id);
            }
        }

        return $data;
    }

    public function save($data)
    {
        // If we are editing an existing property, don't allow the article_id to be changed.
        if (!empty($data['id'])) {
            unset($data['article_id']);
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
                $ratesData = [
                    'property_id' => $propertyId,
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

        // Get the article_ids for all properties being deleted before we delete them
        $query = $db->getQuery(true)
            ->select('article_id')
            ->from($db->quoteName('#__bookingmanager_properties'))
            ->where('id IN (' . implode(',', array_map('int', $pks)) . ')');
        $articleIds = $db->setQuery($query)->loadColumn();
        $articleIds = array_filter($articleIds); // Remove any nulls or zeros

        // Clean up related data first
        foreach ($pks as $pk) {
            // Delete from complex map table
            $query->clear()
                ->delete($db->quoteName('#__bookingmanager_complex_property_map'))
                ->where('property_id = ' . (int)$pk);
            $db->setQuery($query)->execute();
        }

        // If there were any linked articles, delete their associated rates
        if (!empty($articleIds)) {
            $query->clear()
                ->delete($db->quoteName('#__bookingmanager_rates'))
                ->where('property_id IN (' . implode(',', $articleIds) . ')');
            $db->setQuery($query)->execute();
        }

        // Finally, call parent delete to remove from the main properties table
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
