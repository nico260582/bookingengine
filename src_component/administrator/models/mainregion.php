<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

class BookingmanagerModelMainregion extends AdminModel
{
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_bookingmanager.mainregion', 'mainregion', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.mainregion.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    public function getTable($type = 'Mainregion', $prefix = 'BookingmanagerTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }
}
