<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;

class BookingmanagerModelComplex extends AdminModel
{
    public function getTable($type = 'Complex', $prefix = 'BookingmanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_bookingmanager.complex',
            'complex',
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
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.complex.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        // Add created_at on new records
        if (empty($data['id'])) {
            $data['created_at'] = Factory::getDate()->toSql();
        }

        return parent::save($data);
    }
}
