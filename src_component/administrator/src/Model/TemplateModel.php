<?php
namespace RTHolidays\Component\BookingManager\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;

class TemplateModel extends AdminModel
{
    public function getTable($type = 'Template', $prefix = 'BookingmanagerTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $this->addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/src/Form');
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