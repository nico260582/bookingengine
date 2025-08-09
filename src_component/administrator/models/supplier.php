<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\Model\AdminModel;

class BookingmanagerModelSupplier extends AdminModel
{
    public function getTable($type = 'Supplier', $prefix = 'BookingmanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    
    public function getForm($data = array(), $loadData = true)
    {
        JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');
        JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
        $form = $this->loadForm('com_bookingmanager.supplier', 'supplier', ['control' => 'jform', 'load_data' => $loadData]);
        return empty($form) ? false : $form;
    }
    
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.supplier.data', array());
        if (empty($data)) {
            $data = $this->getItem();
            if ($data && !empty($data->rules)) {
                $rules = json_decode($data->rules);
                if (is_object($rules)) {
                    foreach ($rules as $key => $value) {
                        if (!isset($data->$key)) {
                            $data->$key = $value;
                        }
                    }
                }
            }
            if ($data && !empty($data->id)) {
                $db = Factory::getDbo();
                $query = $db->getQuery(true)->select('property_id')->from('#__bookingmanager_property_map')->where('supplier_id = ' . (int)$data->id);
                $data->properties = $db->setQuery($query)->loadColumn();
            }
        }
        return $data;
    }
    
    public function getChangeLog($supplierId)
    {
        if (!$supplierId) { return []; }
        $db = Factory::getDbo();
        $query = $db->getQuery(true)->select('*')->from('#__booking_supplier_logs')->where('supplier_id = ' . (int)$supplierId)->order('created_at DESC');
        return $db->setQuery($query)->loadObjectList();
    }
    
    public function save($data)
    {
        $table = $this->getTable();
        $pkValue = $data['id'] ?? 0;
        $oldData = null;
        if ($pkValue) {
            if ($table->load($pkValue)) {
                $oldData = $table->getProperties();
            }
        }

        $rules = new stdClass();
        $rule_fields = ['pricing_model', 'adult_supplement', 'child_supplement', 'extra_mattress_fee', 'seasons', 'infant_max_age', 'child_max_age', 'teen_max_age', 'free_with_parents_age', 'country_discounts', 'coupon_codes'];
        foreach ($rule_fields as $field) {
            if (isset($data[$field])) {
                $rules->$field = $data[$field];
                unset($data[$field]);
            }
        }
        $data['rules'] = json_encode($rules);
        
        $assignedProperties = $data['properties'] ?? [];
        unset($data['properties']);

        if (parent::save($data)) {
            $id = (int) $this->getState($this->getName() . '.id');
            if ($oldData) {
                $table->load($id);
                $newData = $table->getProperties();
                $this->logChanges($id, $oldData, $newData);
            }
            $db = Factory::getDbo();
            $query = $db->getQuery(true)->delete('#__bookingmanager_property_map')->where('supplier_id = ' . $id);
            $db->setQuery($query)->execute();

            if (!empty($assignedProperties)) {
                $query->clear()->insert('#__bookingmanager_property_map')->columns(['property_id', 'supplier_id']);
                foreach ($assignedProperties as $propId) {
                    $query->values((int)$propId . ',' . $id);
                }
                $db->setQuery($query)->execute();
            }
            return true;
        }
        return false;
    }

    public function getAllPropertiesWithAssignments($currentSupplierId = 0)
    {
        $db = Factory::getDbo();

        // 1. Get all properties (Joomla articles)
        $user = Factory::getUser();
        $now  = Factory::getDate()->toSql();
        $nullDate = $db->getNullDate();
        $query = $db->getQuery(true)
            ->select('a.id, a.title')
            ->from($db->quoteName('#__content', 'a'))
            ->where('a.state = 1')
            ->where('a.catid > 0')
            ->where('a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
            ->where("a.publish_up <= " . $db->quote($now))
            ->where("(a.publish_down = " . $db->quote($nullDate) . " OR a.publish_down >= " . $db->quote($now) . ")")
            ->order('a.title');
        $allProperties = $db->setQuery($query)->loadObjectList('id');

        // 2. Get all current assignments with supplier abbreviations
        $query->clear()
            ->select('m.property_id, m.supplier_id, s.abbreviation')
            ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
            ->join('LEFT', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id');
        $assignments = $db->setQuery($query)->loadObjectList('property_id');

        // 3. Combine the data
        foreach ($allProperties as $id => &$property) {
            $property->assignment = null;
            if (isset($assignments[$id])) {
                $property->assignment = [
                    'supplier_id' => $assignments[$id]->supplier_id,
                    'abbreviation' => $assignments[$id]->abbreviation,
                    'is_current' => ($assignments[$id]->supplier_id == $currentSupplierId)
                ];
            }
        }
        return $allProperties;
    }
    
    private function logChanges($supplierId, $oldData, $newData)
    {
        $user = Factory::getUser();
        $db = $this->getDbo();
        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $oldData) && $oldData[$key] != $value) {
                $log = new \stdClass();
                $log->supplier_id = $supplierId;
                $log->created_at  = (new Date('now'))->toSql();
                $log->user_id     = $user->id;
                $log->user_name   = $user->name;
                $log->field_name  = $key;
                $log->old_value   = is_string($oldData[$key]) ? $oldData[$key] : json_encode($oldData[$key]);
                $log->new_value   = is_string($value) ? $value : json_encode($value);
                $db->insertObject('#__booking_supplier_logs', $log);
            }
        }
    }
}