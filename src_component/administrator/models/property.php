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

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.property.data', array());

        if (empty($data)) {
            $data = $this->getItem();
            if ($data->id) {
                // Load the complex ID from the mapping table
                $db = Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select('complex_id')
                    ->from('#__bookingmanager_complex_property_map')
                    ->where('property_id = ' . (int)$data->id);
                $data->complex_id = $db->setQuery($query)->loadResult();

                // Load the rates data
                AdminModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
                $ratesModel = AdminModel::getInstance('Propertyrates', 'BookingmanagerModel');
                if ($ratesModel) {
                    $data->ratesData = $ratesModel->getRateData($data->id);
                }
            }
        }

        return $data;
    }

    public function save($data)
    {
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
        foreach ($pks as $pk) {
            // Delete from complex map table
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__bookingmanager_complex_property_map'))
                ->where('property_id = ' . (int)$pk);
            $db->setQuery($query)->execute();

            // Delete from rates table
            $query->clear()
                ->delete($db->quoteName('#__bookingmanager_rates'))
                ->where('property_id = ' . (int)$pk);
            $db->setQuery($query)->execute();
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
