<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Date\Date;

class BookingmanagerModelCommunication extends BaseDatabaseModel
{
    public function getRequestData($requestId)
    {
        if (!$requestId)
        {
            return null;
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__booking_requests'))
            ->where($db->quoteName('id') . ' = ' . (int) $requestId);

        return $db->setQuery($query)->loadObject();
    }

    public function getMessages($requestId)
    {
        if (!$requestId)
        {
            return [];
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__booking_communication'))
            ->where($db->quoteName('request_id') . ' = ' . (int) $requestId)
            ->order($db->quoteName('created_at') . ' DESC');

        return $db->setQuery($query)->loadObjectList();
    }

    public function getAttachments($requestId)
    {
        if (!$requestId) { return []; }
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select(['id', 'file_name', 'created_at', 'uploaded_by', 'file_path'])
            ->from($db->quoteName('#__booking_attachments'))
            ->where('request_id = ' . (int)$requestId)
            ->order('created_at DESC');
        return $db->setQuery($query)->loadObjectList();
    }

    public function validateLogin($email, $pin)
    {
        if (empty($email) || empty($pin))
        {
            return false;
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__booking_requests'))
            ->where($db->quoteName('client_email') . ' = ' . $db->quote($email))
            ->where($db->quoteName('pin') . ' = ' . $db->quote($pin));

        $requestId = (int) $db->setQuery($query)->loadResult();

        return $requestId > 0 ? $requestId : false;
    }

    public function saveClientMessage($requestId, $message)
    {
        if (!$requestId || empty($message))
        {
            return false;
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('client_name'))
            ->from($db->quoteName('#__booking_requests'))
            ->where($db->quoteName('id') . ' = ' . (int) $requestId);

        $clientName = $db->setQuery($query)->loadResult();

        if (!$clientName)
        {
            return false;
        }

        $table = JTable::getInstance('Communication', 'BookingmanagerTable');
        $data = [
            'request_id' => $requestId,
            'created_at' => (new Date('now'))->toSql(),
            'author'     => $clientName . ' (Client)',
            'message'    => $message
        ];

        return $table->save($data);
    }

    public function getRequestsForUser($userId)
    {
        if (!$userId)
        {
            return [];
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__booking_requests'))
            ->where($db->quoteName('user_id') . ' = ' . (int) $userId)
            ->order($db->quoteName('created_at') . ' DESC');

        return $db->setQuery($query)->loadObjectList();
    }
}