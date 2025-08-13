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
}
