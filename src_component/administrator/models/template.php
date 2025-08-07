<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class BookingmanagerModelTemplate extends AdminModel
{
    public function getTable($type = 'Template', $prefix = 'BookingmanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');
        $form = $this->loadForm('com_bookingmanager.template', 'template', ['control' => 'jform', 'load_data' => $loadData]);
        return empty($form) ? false : $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.template.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }
        return $data;
    }
}