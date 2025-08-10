<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\FormController;

class BookingmanagerControllerBookingrequest extends FormController
{
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app   = Factory::getApplication();
        $input = $app->input;
        $data  = $input->post->get('jform', array(), 'array');
        $files = $input->files->get('jform');
        $model = $this->getModel('Bookingrequest', 'BookingmanagerModel');

        // First, save the main form data using the model.
        if (!$model->save($data)) {
            $this->setError($model->getError());
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequest&layout=edit&id=' . (int)$data['id'], false));
            return false;
        }

        // Get the ID of the item we just saved.
        $requestId = $model->getState('bookingrequest.id');

        
        // Set the success message and redirect.
        $this->setMessage(JText::_('COM_BOOKINGMANAGER_ITEM_SAVED_SUCCESSFULLY'));
        $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequests', false));
    }

   private function uploadAttachment($requestId, $messageId, $file, $uploaderName)
    {
        if (!$requestId || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $app = Factory::getApplication();
        $filename = File::makeSafe($file['name']);
        $dest_path = JPATH_SITE . '/media/com_bookingmanager/attachments/' . $requestId;

        if (!Folder::exists($dest_path)) {
            if (!Folder::create($dest_path)) {
                $app->enqueueMessage('Error: Could not create attachment directory.', 'error');
                return false;
            }
        }
        
        $dest_file = $dest_path . '/' . $filename;

        if (File::upload($file['tmp_name'], $dest_file)) {
            $db = Factory::getDbo();
            $attachment = new stdClass();
            $attachment->request_id = (int) $requestId;
            $attachment->message_id = (int) $messageId;
            $attachment->file_name = $filename;
            $attachment->file_path = 'media/com_bookingmanager/attachments/' . $requestId . '/' . $filename;
            $attachment->uploaded_by = $uploaderName;
            $attachment->created_at = (new Date('now'))->toSql();
            
            if (!$db->insertObject('#__booking_attachments', $attachment)) {
                $app->enqueueMessage('Database error: Could not save attachment record.', 'error');
                return false;
            }
            return true;
        } else {
            $app->enqueueMessage('File upload failed.', 'error');
            return false;
        }
    }

    public function addmessage()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app        = Factory::getApplication();
        $input      = $app->input;
        $user       = Factory::getUser();
        
        $jform      = $input->post->get('jform', [], 'array');
        $files      = $input->files->get('jform');
        $message    = $jform['admin_message'] ?? '';
        $requestId  = (int)($jform['id'] ?? 0);
        $attachmentFile = $files['attachment'] ?? null;
        
        $redirectUrl = Route::_('index.php?option=com_bookingmanager&view=bookingrequest&layout=edit&id=' . $requestId, false);

        if (empty($requestId) || (empty($message) && (empty($attachmentFile) || $attachmentFile['error'] !== UPLOAD_ERR_OK))) {
            $this->setRedirect($redirectUrl, 'Message or attachment cannot be empty.', 'error');
            return;
        }

        $table = JTable::getInstance('Communication', 'BookingmanagerTable');
        $saveData = [
            'request_id' => $requestId,
            'created_at' => (new Date('now'))->toSql(),
            'author'     => $user->name . ' (Admin)',
            'message'    => $message ?: ''
        ];

        if (!$table->save($saveData)) {
             $app->enqueueMessage($table->getError(), 'error');
        } else {
            $messageId = $table->id;
            if (!empty($message)) {
                JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
                BookingmanagerHelper::sendNotificationEmails($requestId, 'email_client_admin_reply', $message);
            }
            if ($attachmentFile && $attachmentFile['error'] === UPLOAD_ERR_OK) {
                $this->uploadAttachment($requestId, $messageId, $attachmentFile, $user->name . ' (Admin)');
            }
            $app->enqueueMessage('Message saved and sent to client successfully.');
        }

        $this->setRedirect($redirectUrl);
    }
    public function uploadAttachment()
    {
        $this->checkToken('post');

        $app = JFactory::getApplication();
        $input = $app->input;
        $file = $input->files->get('attachment');
        $id = $input->getInt('id');

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            echo new JResponseJson(null, 'No file uploaded or upload error.', true);
            $app->close();
        }

        $filename = JFile::makeSafe($file['name']);
        $filepath = JPATH_ROOT . '/media/com_bookingmanager/attachments/' . $id . '/' . $filename;

        if (!JFolder::exists(dirname($filepath))) {
            JFolder::create(dirname($filepath));
        }

        if (JFile::upload($file['tmp_name'], $filepath)) {
            $data = ['filePath' => 'media/com_bookingmanager/attachments/' . $id . '/' . $filename];
            echo new JResponseJson($data);
        } else {
            echo new JResponseJson(null, 'Failed to move uploaded file.', true);
        }

        $app->close();
    }

    public function addmessage()
    {
        $this->checkToken();

        $app = JFactory::getApplication();
        $input = $app->input;
        $data = $input->post->get('jform', [], 'array');
        $id = $input->getInt('id');

        $model = $this->getModel();

        if ($model->addAdminMessage($id, $data['admin_message'], $data['uploaded_attachments'] ?? [])) {
            $this->setRedirect(JRoute::_('index.php?option=com_bookingmanager&view=bookingrequest&id=' . $id, false), 'Message sent.');
        } else {
            $this->setRedirect(JRoute::_('index.php?option=com_bookingmanager&view=bookingrequest&id=' . $id, false), 'Error sending message.', 'error');
        }
    }
}