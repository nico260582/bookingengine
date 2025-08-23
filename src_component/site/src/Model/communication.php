<?php
namespace RTHolidays\Component\BookingManager\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Table\Table;

class CommunicationModel extends BaseDatabaseModel
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

    public function saveClientMessage($requestId, $message, $attachments = [])
    {
        if (!$requestId) {
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

        $table = Table::getInstance('Communication', 'RTHolidays\\Component\\BookingManager\\Administrator\\Table');
        $data = [
            'request_id' => $requestId,
            'created_at' => (new Date('now'))->toSql(),
            'author'     => $clientName . ' (Client)',
            'message'    => $message ?: '' // Ensure message is not null
        ];

        if (!$table->save($data)) {
            return false;
        }

        $messageId = $table->id;
        if (!empty($attachments)) {
            $db = $this->getDbo();
            foreach ($attachments as $attachmentPath) {
                $attachment = new \stdClass();
                $attachment->request_id = $requestId;
                $attachment->message_id = $messageId;
                $attachment->file_name = basename($attachmentPath);
                $attachment->file_path = $attachmentPath;
                $attachment->created_at = (new Date('now'))->toSql();
                $attachment->uploaded_by = $clientName . ' (Client)';
                $db->insertObject('#__booking_attachments', $attachment);
            }
        }

        return true;
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