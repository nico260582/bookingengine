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

    public function uploadAttachment()
    {
        Factory::getApplication()->input->post->set('jform', ['id' => Factory::getApplication()->input->getInt('id')]);
        parent::checkToken('post');

        $app = Factory::getApplication();
        $input = $app->input;
        $file = $input->files->get('attachment');
        $id = $input->getInt('id');

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('COM_BOOKINGMANAGER_ERROR_NO_FILE_UPLOADED'), true);
            $app->close();
        }

        $filename = File::makeSafe($file['name']);
        $filepath = JPATH_ROOT . '/media/com_bookingmanager/attachments/' . $id . '/' . $filename;

        if (!Folder::exists(dirname($filepath))) {
            Folder::create(dirname($filepath));
        }

        if (File::upload($file['tmp_name'], $filepath)) {
            $data = ['filePath' => 'media/com_bookingmanager/attachments/' . $id . '/' . $filename];
            echo new \Joomla\CMS\Response\JsonResponse($data);
        } else {
            echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('COM_BOOKINGMANAGER_ERROR_FAILED_TO_MOVE_UPLOADED_FILE'), true);
        }

        $app->close();
    }

    public function addmessage()
    {
        parent::checkToken();

        $app = Factory::getApplication();
        $input = $app->input;
        $jform = $input->post->get('jform', [], 'array');
        $id = $input->getInt('id');
        $uploadedAttachments = json_decode($input->post->get('uploaded_attachments', '[]', 'raw'), true);

        $model = $this->getModel();

        if ($model->addAdminMessage($id, $jform['admin_message'], $uploadedAttachments)) {
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequest&layout=edit&id=' . $id, false), 'Message sent.');
        } else {
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequest&layout=edit&id=' . $id, false), 'Error sending message.', 'error');
        }
    }
}