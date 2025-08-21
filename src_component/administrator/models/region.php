<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;

class BookingmanagerModelRegion extends AdminModel
{
    public function getTable($type = 'Region', $prefix = 'BookingmanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_bookingmanager.region',
            'region',
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
            'com_bookingmanager.edit.region.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        if (parent::save($data)) {
            return true;
        }

        return false;
    }

    public function delete(&$pks)
    {
        $db = $this->getDbo();
        foreach ($pks as $pk) {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bookingmanager_regions'))
                ->where('parent_id = ' . (int)$pk);
            $db->setQuery($query);
            $childCount = $db->loadResult();

            if ($childCount > 0) {
                $this->setError('Cannot delete a main region that has sub-regions.');
                return false;
            }
        }

        return parent::delete($pks);
    }
}
