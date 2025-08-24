<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

class BookingmanagerModelSubregion extends AdminModel
{
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_bookingmanager.subregion',
            'subregion',
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
        $data = Factory::getApplication()->getUserState(
            'com_bookingmanager.edit.subregion.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        $table = $this->getTable();
        $pkName = $table->getKeyName();
        $pk = (!empty($data[$pkName])) ? $data[$pkName] : 0;

        try {
            if ($pk > 0) {
                $table->load($pk);
            }

            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $this->cleanCache();
        return true;
    }
}
