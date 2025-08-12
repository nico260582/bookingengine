<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

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
        if ($pkValue && $table->load($pkValue)) {
            $oldData = $table->getProperties();
        }

        // Extract assigned properties before they are unset
        $assignedProperties = $data['properties'] ?? [];
        unset($data['properties']);

        // Prepare the rules data
        $this->prepareRules($data);

        if (parent::save($data)) {
            $id = (int) $this->getState($this->getName() . '.id');

            if ($oldData) {
                $table->load($id);
                $newData = $table->getProperties();
                $this->logChanges($id, $oldData, $newData);
            }

            $this->updatePropertyAssignments($id, $assignedProperties);

            return true;
        }
        return false;
    }

    private function prepareRules(array &$data)
    {
        $rules = new stdClass();
        $rule_fields = ['pricing_model', 'adult_supplement', 'child_supplement', 'extra_mattress_fee', 'seasons', 'infant_max_age', 'child_max_age', 'teen_max_age', 'free_with_parents_age', 'country_discounts', 'coupon_codes'];
        foreach ($rule_fields as $field) {
            if (isset($data[$field])) {
                $rules->$field = $data[$field];
                unset($data[$field]);
            }
        }
        $data['rules'] = json_encode($rules);
    }

    private function updatePropertyAssignments(int $supplierId, array $assignedProperties)
    {
        $db = Factory::getDbo();
        
        // Delete existing assignments
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__bookingmanager_property_map'))
            ->where($db->quoteName('supplier_id') . ' = ' . $supplierId);
        $db->setQuery($query)->execute();

        // Insert new assignments if any
        if (!empty($assignedProperties)) {
            $insertQuery = $db->getQuery(true)
                ->insert($db->quoteName('#__bookingmanager_property_map'))
                ->columns([$db->quoteName('property_id'), $db->quoteName('supplier_id')]);

            foreach ($assignedProperties as $propId) {
                $insertQuery->values((int)$propId . ',' . $supplierId);
            }
            $db->setQuery($insertQuery)->execute();
        }
    }

    public function getAllPropertiesWithAssignments($currentSupplierId = 0)
    {
        $db = Factory::getDbo();

        // 1. Get all properties (Joomla articles)
        $user = Factory::getUser();
        $now  = Factory::getDate()->toSql();
        $nullDate = $db->getNullDate();

        $params = ComponentHelper::getParams('com_bookingmanager');
        $selectedCategories = $params->get('property_categories', []);

        $allCategoryIds = [];
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            // Sanitize to ensure we have an array of integers
            $categoryIds = array_map('intval', $selectedCategories);

            // Get the lft and rgt values for the selected categories
            $rangesQuery = $db->getQuery(true)
                ->select('c.lft, c.rgt')
                ->from($db->quoteName('#__categories', 'c'))
                ->where('c.id IN (' . implode(',', $categoryIds) . ')');
            $ranges = $db->setQuery($rangesQuery)->loadObjectList();

            if ($ranges) {
                $whereClauses = [];
                foreach ($ranges as $range) {
                    $whereClauses[] = '(c.lft >= ' . $range->lft . ' AND c.rgt <= ' . $range->rgt . ')';
                }

                // Get all categories (including sub-categories) within the selected category trees
                $subCategoriesQuery = $db->getQuery(true)
                    ->select('c.id')
                    ->from($db->quoteName('#__categories', 'c'))
                    ->where('(' . implode(' OR ', $whereClauses) . ')')
                    ->where("c.extension = 'com_content'");

                $allCategoryIds = $db->setQuery($subCategoriesQuery)->loadColumn();
            }
        }

        $query = $db->getQuery(true)
            ->select('a.id, a.title')
            ->from($db->quoteName('#__content', 'a'))
            ->where('a.state = 1')
            ->where('a.catid > 0')
            ->where('a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
            ->where("a.publish_up <= " . $db->quote($now))
            ->where("(a.publish_down IS NULL OR a.publish_down = " . $db->quote($nullDate) . " OR a.publish_down >= " . $db->quote($now) . ")");

        if (!empty($allCategoryIds)) {
            $query->where('a.catid IN (' . implode(',', $allCategoryIds) . ')');
        }

        $query->order('a.title');
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

        foreach ($newData as $key => $newValue) {
            if (!array_key_exists($key, $oldData) || $oldData[$key] == $newValue) {
                continue; // Skip if key is new or value is unchanged
            }

            $oldValue = $oldData[$key];

            if ($key === 'rules') {
                $oldRules = json_decode($oldValue, true);
                $newRules = json_decode($newValue, true);

                foreach ($newRules as $ruleKey => $ruleValue) {
                    $oldRuleValue = $oldRules[$ruleKey] ?? null;

                    // A simple way to check if complex fields have changed is to compare their JSON representations.
                    if (json_encode($oldRuleValue) !== json_encode($ruleValue)) {
                        if (is_array($ruleValue)) {
                             $this->createLogEntry($supplierId, $user, "rules." . $ruleKey, Text::_('COM_BOOKINGMANAGER_LOG_COMPLEX_DATA_CHANGED'), Text::_('COM_BOOKINGMANAGER_LOG_COMPLEX_DATA_CHANGED'));
                        } else {
                            $this->createLogEntry($supplierId, $user, "rules." . $ruleKey, (string)$oldRuleValue, (string)$ruleValue);
                        }
                    }
                }
            } else {
                // Standard logging for non-rules fields
                $this->createLogEntry($supplierId, $user, $key, (string)$oldValue, (string)$newValue);
            }
        }
    }

    private function createLogEntry($supplierId, $user, $fieldName, $oldValue, $newValue)
    {
        $log = new \stdClass();
        $log->supplier_id = $supplierId;
        $log->created_at  = (new Date('now'))->toSql();
        $log->user_id     = $user->id;
        $log->user_name   = $user->name;
        $log->field_name  = $fieldName;
        $log->old_value   = substr((string)$oldValue, 0, 1024);
        $log->new_value   = substr((string)$newValue, 0, 1024);
        $this->getDbo()->insertObject('#__booking_supplier_logs', $log);
    }
}