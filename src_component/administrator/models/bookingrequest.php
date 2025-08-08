<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\Model\AdminModel;

class BookingmanagerModelBookingrequest extends AdminModel
{
    public function getTable($type = 'Bookingrequest', $prefix = 'BookingmanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    
    public function getForm($data = array(), $loadData = true)
    {
        JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');
        $form = $this->loadForm('com_bookingmanager.bookingrequest', 'bookingrequest', ['control' => 'jform', 'load_data' => $loadData]);
        return empty($form) ? false : $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.bookingrequest.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }
        return $data;
    }

    public function getMessages($requestId)
    {
        if (!$requestId) { return []; }
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__booking_communication')
            ->where('request_id = ' . (int)$requestId)
            ->order('created_at DESC');

        $messages = $db->setQuery($query)->loadObjectList('id');

        if (empty($messages)) {
            return [];
        }

        $messageIds = array_keys($messages);
        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__booking_attachments'))
            ->where($db->quoteName('message_id') . ' IN (' . implode(',', $messageIds) . ')');

        $attachments = $db->setQuery($query)->loadObjectList();

        foreach ($attachments as $attachment) {
            if (isset($messages[$attachment->message_id])) {
                if (!isset($messages[$attachment->message_id]->attachments)) {
                    $messages[$attachment->message_id]->attachments = [];
                }
                $messages[$attachment->message_id]->attachments[] = $attachment;
            }
        }

        return array_values($messages);
    }

    public function getChangeLog($requestId)
    {
        if (!$requestId) { return []; }
        $db = Factory::getDbo();
        $query = $db->getQuery(true)->select('*')->from('#__booking_request_logs')->where('request_id = ' . (int)$requestId)->order('created_at DESC');
        return $db->setQuery($query)->loadObjectList();
    }
    
    public function getAttachments($requestId)
    {
        if (!$requestId) { return []; }
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(['id', 'file_name', 'created_at', 'uploaded_by', 'file_path'])
            ->from($db->quoteName('#__booking_attachments'))
            ->where('request_id = ' . (int)$requestId)
            ->order('created_at DESC');
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
        if (parent::save($data)) {
            $pkValue = $this->getState($this->getName() . '.id');
            if ($oldData) {
                $table->load($pkValue);
                $newData = $table->getProperties();
                $this->logChanges($pkValue, $oldData, $newData);
            }
            return true;
        }
        return false;
    }

    private function logChanges($requestId, $oldData, $newData)
    {
        $user = Factory::getUser();
        $db = $this->getDbo();
        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $oldData) && $oldData[$key] != $value) {
                $log = new \stdClass();
                $log->request_id = $requestId;
                $log->created_at = (new Date('now'))->toSql();
                $log->user_id    = $user->id;
                $log->user_name  = $user->name;
                $log->field_name = $key;
                $log->old_value  = (string) $oldData[$key];
                $log->new_value  = (string) $value;
                $db->insertObject('#__booking_request_logs', $log);
            }
        }
    }
}