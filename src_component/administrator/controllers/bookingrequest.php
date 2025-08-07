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

        // Now, check for and process the uploaded file.
        if (!empty($files['attachment']['name']) && $files['attachment']['error'] === UPLOAD_ERR_OK) {
            $user = Factory::getUser();
            if (!$this->uploadAttachment($requestId, $files['attachment'], $user->name . ' (Admin)')) {
                // The uploadAttachment function will enqueue its own error message.
            } else {
                $app->enqueueMessage('Attachment uploaded successfully.');
            }
        }
        
        // Set the success message and redirect.
        $this->setMessage(JText::_('COM_BOOKINGMANAGER_ITEM_SAVED_SUCCESSFULLY'));
        $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequests', false));
    }

   private function uploadAttachment($requestId, $file, $uploaderName)
    {
        if (!$requestId || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $app = Factory::getApplication();
        $filename = File::makeSafe($file['name']);
        // Define the destination path for the attachments
        $dest_path = JPATH_SITE . '/media/com_bookingmanager/attachments/' . $requestId;

        // Check if the base directory exists and is writable, if not, try to create it.
        if (!Folder::exists($dest_path)) {
            if (!Folder::create($dest_path)) {
                $app->enqueueMessage('Error: Could not create attachment directory. Please check permissions for the /media/com_bookingmanager/ folder.', 'error');
                return false;
            }
        }
        
        $dest_file = $dest_path . '/' . $filename;

        if (File::upload($file['tmp_name'], $dest_file)) {
            $db = Factory::getDbo();
            $attachment = new stdClass();
            $attachment->request_id = (int) $requestId;
            $attachment->file_name = $filename;
            // The path stored in the database should be relative to the site root
            $attachment->file_path = 'media/com_bookingmanager/attachments/' . $requestId . '/' . $filename;
            $attachment->uploaded_by = $uploaderName;
            $attachment->created_at = (new Date('now'))->toSql();
            
            if (!$db->insertObject('#__booking_attachments', $attachment)) {
                $app->enqueueMessage('Database error: Could not save attachment record.', 'error');
                return false;
            }
            return true;
        } else {
            $app->enqueueMessage('File upload failed. Please check server permissions and file size limits.', 'error');
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
        $message    = $jform['admin_message'] ?? '';
        $requestId  = (int)($jform['id'] ?? 0);
        
        $redirectUrl = Route::_('index.php?option=com_bookingmanager&view=bookingrequest&layout=edit&id=' . $requestId, false);

        if (empty($message) || empty($requestId)) {
            $this->setRedirect($redirectUrl, 'Message cannot be empty.', 'error');
            return;
        }

        $table = JTable::getInstance('Communication', 'BookingmanagerTable');
        $saveData = [
            'request_id' => $requestId,
            'created_at' => (new Date('now'))->toSql(),
            'author'     => $user->name . ' (Admin)',
            'message'    => $message
        ];

        if (!$table->save($saveData)) {
             $app->enqueueMessage($table->getError(), 'error');
        } else {
            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
            BookingmanagerHelper::sendNotificationEmails($requestId, 'email_client_admin_reply', $message);
            $app->enqueueMessage('Message saved and sent to client successfully.');
        }

        $this->setRedirect($redirectUrl);
    }
}