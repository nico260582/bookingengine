<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\BaseController;

class BookingmanagerControllerCommunication extends BaseController
{
    public function login()
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $session = Factory::getSession();

        if (!Session::checkToken('post')) {
            $app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
            return;
        }

        $email = $input->post->getString('email');
        $pin   = $input->post->getString('pin');

        $model = $this->getModel('Communication', 'BookingmanagerModel');
        $requestId = $model->validateLogin($email, $pin);

        if ($requestId) {
            $session->set('bookingmanager_request_id', $requestId);
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
        } else {
            $app->enqueueMessage(JText::_('COM_BOOKINGMANAGER_CLIENT_PORTAL_ERROR_NOT_FOUND'), 'error');
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
        }
    }

    public function logout()
    {
        $session = Factory::getSession();
        $session->clear('bookingmanager_request_id');

        $user = Factory::getUser();
        if (!$user->guest)
        {
            $app = Factory::getApplication();
            $app->logout($user->id);
        }

        Factory::getApplication()->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
    }

    public function switchBooking()
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $session = Factory::getSession();
        $user = Factory::getUser();

        if (!Session::checkToken('post')) {
            $app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
            return;
        }

        $requestId = $input->post->getInt('request_id');

        // Security check: ensure the requested booking belongs to the logged-in user
        if (!$user->guest && $requestId) {
            $model = $this->getModel('Communication', 'BookingmanagerModel');
            $userRequests = $model->getRequestsForUser($user->id);
            $isAllowed = false;
            foreach ($userRequests as $request) {
                if ($request->id == $requestId) {
                    $isAllowed = true;
                    break;
                }
            }

            if ($isAllowed) {
                $session->set('bookingmanager_request_id', $requestId);
            } else {
                $app->enqueueMessage('You do not have permission to view this booking.', 'error');
            }
        }

        Factory::getApplication()->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
    }
    
    public function addClientMessage()
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $session = Factory::getSession();

        if (!Session::checkToken('post')) {
            $app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
            return;
        }

        $requestId = $session->get('bookingmanager_request_id');
        $message   = $input->post->get('message', '', 'raw');
        $file      = $input->files->get('attachment');

        if (!$requestId || (empty($message) && (empty($file) || $file['error'] !== UPLOAD_ERR_OK))) {
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
            return;
        }
        
        $model = $this->getModel('Communication', 'BookingmanagerModel');
        $messageId = null;

        if (!empty($message) || (!empty($file) && $file['error'] === UPLOAD_ERR_OK)) {
            $messageId = $model->saveClientMessage($requestId, $message);

            if ($messageId) {
                if (!empty($message)) {
                    JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
                    BookingmanagerHelper::sendNotificationEmails($requestId, 'email_admin_client_reply', $message);
                }

                if (!empty($file) && $file['error'] === UPLOAD_ERR_OK) {
                    $clientName = $model->getRequestData($requestId)->client_name;
                    $this->uploadAttachment($requestId, $messageId, $file, $clientName . ' (Client)');
                }
            } else {
                $app->enqueueMessage('There was an error saving your message.', 'error');
            }
        }

        $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
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
}