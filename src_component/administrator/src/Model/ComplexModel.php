<?php
namespace RTHolidays\Component\BookingManager\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Date\Date;

class ComplexModel extends AdminModel
{
    public function getTable($type = 'Complex', $prefix = 'BookingmanagerTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
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
        // Generate an alias from the name if it's not present.
        if (empty($data['alias']) && !empty($data['name'])) {
            $data['alias'] = OutputFilter::stringURLSafe($data['name']);
        }

        // Add created_at on new records
        if (empty($data['id'])) {
            $data['created_at'] = (new Date('now'))->toSql();
        }

        return parent::save($data);
    }

    public function delete(&$pks)
    {
        $db = $this->getDbo();
        foreach ($pks as $pk) {
            // Delete from complex map table
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__bookingmanager_complex_property_map'))
                ->where('complex_id = ' . (int)$pk);
            $db->setQuery($query)->execute();
        }

        // Call parent delete to remove from main complexes table
        return parent::delete($pks);
    }
}
