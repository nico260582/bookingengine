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

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item) {
            // Get supplier details for the communication tab
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('s.contact_phone')
                ->from($db->quoteName('#__bookingmanager_suppliers', 's'))
                ->join('LEFT', $db->quoteName('#__bookingmanager_property_map', 'm') . ' ON s.id = m.supplier_id')
                ->join('LEFT', $db->quoteName('#__content', 'p') . ' ON m.property_id = p.id')
                ->where('p.title = ' . $db->quote($item->property_name));

            $supplierDetails = $db->setQuery($query)->loadObject();
            if ($supplierDetails) {
                $item->supplier_contact_phone = $supplierDetails->contact_phone;
            }
        }

        if ($item && !empty($item->terms_log_id)) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('terms_content'))
                ->from($db->quoteName('#__bookingmanager_terms_log'))
                ->where($db->quoteName('id') . ' = ' . (int) $item->terms_log_id);

            $item->terms_content = $db->setQuery($query)->loadResult();
        } else if ($item) {
            $item->terms_content = null;
        }

        return $item;
    }

    public function getSupplierTemplate()
    {
        $app = Factory::getApplication();
        $requestId = $app->input->getInt('id', 0);

        if (!$requestId) {
            return false;
        }

        JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
        return BookingmanagerHelper::getProcessedSupplierTemplateBody($requestId);
    }

    public function getSupplierMessages($requestId)
    {
        if (!$requestId) {
            return [];
        }
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('sc.*, u.name as author_name')
            ->from($db->quoteName('#__booking_supplier_communication', 'sc'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON sc.sent_by_user_id = u.id')
            ->where('sc.booking_request_id = ' . (int)$requestId)
            ->order('sc.sent_at DESC');
        return $db->setQuery($query)->loadObjectList();
    }

    public function sendSupplierMessage($requestId, $message, $whatsappSent = false)
    {
        if (!$requestId || (empty($message) && !$whatsappSent)) {
            $this->setError('No message content and WhatsApp not marked as sent.');
            return false;
        }

        $db = Factory::getDbo();
        $user = Factory::getUser();

        // 1. Get Property from Booking Request to find the supplier
        $query = $db->getQuery(true)
            ->select($db->quoteName('property_name'))
            ->from($db->quoteName('#__booking_requests'))
            ->where($db->quoteName('id') . ' = ' . (int)$requestId);
        $propertyName = $db->setQuery($query)->loadResult();

        if (!$propertyName) {
            $this->setError('Could not find property for the booking request.');
            return false;
        }

        // 2. Get Supplier from Property
        $query->clear()
            ->select('s.id, s.contact_email, s.contact_phone')
            ->from($db->quoteName('#__bookingmanager_suppliers', 's'))
            ->join('LEFT', $db->quoteName('#__bookingmanager_property_map', 'm') . ' ON s.id = m.supplier_id')
            ->join('LEFT', $db->quoteName('#__content', 'p') . ' ON m.property_id = p.id')
            ->where('p.title = ' . $db->quote($propertyName));

        $supplier = $db->setQuery($query)->loadObject();

        if (!$supplier) {
            $this->setError('Could not find a supplier for this property.');
            return false;
        }

        // If the intention is to send an email, ensure there is an email address.
        if (!empty($message) && empty($supplier->contact_email)) {
            $this->setError('Unable to send email: This supplier does not have a contact email address.');
            return false;
        }

        // 3. Save the message to the database

        // Proactively check message length to prevent database errors for oversized content.
        // A standard TEXT column in MySQL has a limit of 65,535 bytes.
        if (strlen($message) > 65000) {
            $this->setError('The message is too long to be saved in the conversation history. Please shorten it and try again.');
            return false;
        }

        $table = JTable::getInstance('SupplierCommunication', 'BookingmanagerTable');

        $logMessage = $message;
        if (empty($logMessage) && $whatsappSent) {
            $logMessage = 'WhatsApp communication sent to supplier.';
        }

        $logData = [
            'booking_request_id' => $requestId,
            'supplier_id'        => $supplier->id,
            'supplier_email'     => $supplier->contact_email,
            'message'            => $logMessage,
            'sent_at'            => (new Date('now'))->toSql(),
            'sent_by_user_id'    => $user->id,
            'whatsapp_sent'      => (int) $whatsappSent,
        ];

        if (BookingmanagerHelper::columnExists('#__booking_supplier_communication', 'supplier_phone')) {
            $logData['supplier_phone'] = $supplier->contact_phone;
        }

        if (!$table->save($logData)) {
            $this->setError('Failed to save supplier message log: ' . $table->getError());
            return false;
        }

        // 4. Send the email, if there is a message and an email address
        if (!empty($message) && !empty($supplier->contact_email)) {
            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');

            // The helper will now create the template if it doesn't exist.
            BookingmanagerHelper::createDefaultTemplates();

            $emailTemplateQuery = $db->getQuery(true)
                ->select(['subject', 'body'])
                ->from($db->quoteName('#__bookingmanager_templates'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('email_supplier_availability'));
            $emailTemplate = $db->setQuery($emailTemplateQuery)->loadObject();

            if ($emailTemplate) {
                // The message from the textarea is the body.
                // The subject is still generated from the template.
                $placeholders = BookingmanagerHelper::getPlaceholdersForRequest($requestId, 'email_supplier_availability', $message);
                $finalSubject = str_replace(array_keys($placeholders), array_values($placeholders), $emailTemplate->subject);

                $mailer = Factory::getMailer();
                $mailer->isHtml(true);
                $mailer->setSender([(string) Factory::getConfig()->get('mailfrom'), (string) Factory::getConfig()->get('fromname')]);
                $mailer->addRecipient($supplier->contact_email);
                $mailer->setSubject($finalSubject);
                $mailer->setBody($message);

                try {
                    $mailer->send();
                } catch (\Exception $e) {
                    $this->setError('Mailer Error: ' . $e->getMessage());
                    \Joomla\CMS\Log\Log::add('Booking Manager email to supplier failed: ' . $e->getMessage(), \Joomla\CMS\Log\Log::ERROR, 'com_bookingmanager');
                }
            } else {
                \Joomla\CMS\Log\Log::add('Booking Manager "email_supplier_availability" template not found.', \Joomla\CMS\Log\Log::WARNING, 'com_bookingmanager');
            }
        }

        return true;
    }

    public function logWhatsAppMessage($requestId, $message)
    {
        if (!$requestId) {
            $this->setError('Invalid request ID.');
            return false;
        }

        $db = Factory::getDbo();
        $user = Factory::getUser();

        // Get supplier ID and email for logging purposes
        // Use the same two-step query as sendSupplierMessage to avoid collation issues.
        $query = $db->getQuery(true)
            ->select($db->quoteName('property_name'))
            ->from($db->quoteName('#__booking_requests'))
            ->where($db->quoteName('id') . ' = ' . (int)$requestId);
        $propertyName = $db->setQuery($query)->loadResult();

        if (!$propertyName) {
            $this->setError('Could not find property for the booking request.');
            return false;
        }

        $query->clear()
            ->select('s.id, s.contact_email, s.contact_phone')
            ->from($db->quoteName('#__bookingmanager_suppliers', 's'))
            ->join('LEFT', $db->quoteName('#__bookingmanager_property_map', 'm') . ' ON s.id = m.supplier_id')
            ->join('LEFT', $db->quoteName('#__content', 'p') . ' ON m.property_id = p.id')
            ->where('p.title = ' . $db->quote($propertyName));
        $supplier = $db->setQuery($query)->loadObject();

        if (!$supplier) {
            $this->setError('Could not find a supplier for this property.');
            return false;
        }

        $logMessage = $message;
        if (empty($logMessage)) {
            $logMessage = 'WhatsApp communication sent to supplier.';
        }

        $table = JTable::getInstance('SupplierCommunication', 'BookingmanagerTable');
        $logData = [
            'booking_request_id' => $requestId,
            'supplier_id'        => $supplier->id,
            'supplier_email'     => $supplier->contact_email,
            'message'            => $logMessage,
            'sent_at'            => (new Date('now'))->toSql(),
            'sent_by_user_id'    => $user->id,
            'whatsapp_sent'      => 1,
        ];

        if (BookingmanagerHelper::columnExists('#__booking_supplier_communication', 'supplier_phone')) {
            $logData['supplier_phone'] = $supplier->contact_phone;
        }

        if (!$table->save($logData)) {
            $this->setError('Failed to save WhatsApp message log: ' . $table->getError());
            return false;
        }

        return true;
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

    public function getActivityLog($requestId)
    {
        if (!$requestId) {
            return [];
        }
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__booking_client_activity_logs'))
            ->where('booking_request_id = ' . (int)$requestId)
            ->order('created_at DESC');
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
        if (empty($data['final_price'])) {
            $data['final_price'] = 0;
        }

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

    public function addAdminMessage($requestId, $message, $attachments = [])
    {
        if (!$requestId || (empty($message) && empty($attachments))) {
            return false;
        }

        $user = JFactory::getUser();
        $table = JTable::getInstance('Communication', 'BookingmanagerTable');
        $data = [
            'request_id' => $requestId,
            'created_at' => (new JDate('now'))->toSql(),
            'author'     => $user->name . ' (Admin)',
            'message'    => $message
        ];

        if (!$table->save($data)) {
            return false;
        }

        $messageId = $table->id;
        if (!empty($attachments)) {
            $db = $this->getDbo();
            foreach ($attachments as $attachmentPath) {
                $attachment = new stdClass();
                $attachment->request_id = $requestId;
                $attachment->message_id = $messageId;
                $attachment->file_name = basename($attachmentPath);
                $attachment->file_path = $attachmentPath;
                $attachment->created_at = (new JDate('now'))->toSql();
                $attachment->uploaded_by = $user->name . ' (Admin)';
                $db->insertObject('#__booking_attachments', $attachment);
            }
        }

        if (!empty($message) || !empty($attachments)) {
            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
            $notificationMessage = !empty($message) ? $message : 'A new file has been uploaded by the admin.';
            $attachmentData = [];
            if (!empty($attachments)) {
                foreach ($attachments as $filePath) {
                    $attachmentData[] = ['name' => basename($filePath)];
                }
            }
            BookingmanagerHelper::sendNotificationEmails($requestId, 'email_client_admin_reply', $notificationMessage, '', $attachmentData);
        }

        return true;
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