<?php
namespace RTHolidays\Component\BookingManager\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

class CommunicationController extends BaseController
{
    public function login()
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $session = Factory::getSession();

        if (!Session::checkToken('post')) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
            return;
        }

        $email = $input->post->getString('email');
        $pin   = $input->post->getString('pin');

        $model = $this->getModel('Communication');
        $requestId = $model->validateLogin($email, $pin);

        if ($requestId) {
            $session->set('bookingmanager_request_id', $requestId);
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
        } else {
            $app->enqueueMessage(Text::_('COM_BOOKINGMANAGER_CLIENT_PORTAL_ERROR_NOT_FOUND'), 'error');
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
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $app->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
            return;
        }

        $requestId = $input->post->getInt('request_id');

        // Security check: ensure the requested booking belongs to the logged-in user
        if (!$user->guest && $requestId) {
            $model = $this->getModel('Communication');
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
    
    public function uploadAttachment()
    {
        Session::checkToken('post') or jexit(Text::_('JINVALID_TOKEN'));

        $app = Factory::getApplication();
        $input = $app->input;
        $file = $input->files->get('attachment');
        $requestId = $input->getInt('request_id');

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            echo new \Joomla\CMS\Response\JsonResponse(null, Text::_('COM_BOOKINGMANAGER_ERROR_NO_FILE_UPLOADED'), true);
            $app->close();
        }

        $filename = File::makeSafe($file['name']);
        $filepath = JPATH_ROOT . '/media/com_bookingmanager/attachments/' . $requestId . '/' . $filename;

        if (!Folder::exists(dirname($filepath))) {
            Folder::create(dirname($filepath));
        }

        if (File::upload($file['tmp_name'], $filepath)) {
            $data = ['filePath' => 'media/com_bookingmanager/attachments/' . $requestId . '/' . $filename];
            echo new \Joomla\CMS\Response\JsonResponse($data);
        } else {
            echo new \Joomla\CMS\Response\JsonResponse(null, Text::_('COM_BOOKINGMANAGER_ERROR_FAILED_TO_MOVE_UPLOADED_FILE'), true);
        }

        $app->close();
    }

    public function addClientMessage()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $app = Factory::getApplication();
        $input = $app->input;
        $message = $input->getString('message');
        $attachments = json_decode($input->get('uploaded_attachments', '[]', 'raw'), true);
        $requestId = $app->getSession()->get('bookingmanager_request_id');

        $model = $this->getModel();
        if ($model->saveClientMessage($requestId, $message, $attachments)) {
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=communication', false), 'Message sent.');
        } else {
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=communication', false), 'Error sending message.', 'error');
        }
    }
}