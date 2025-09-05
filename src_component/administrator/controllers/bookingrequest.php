<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

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

        $this->setMessage(JText::_('COM_BOOKINGMANAGER_ITEM_SAVED_SUCCESSFULLY'));

        // Check the task to determine the redirect.
        $task = $this->getTask();
        if ($task == 'apply') {
            // Redirect back to the edit view for 'apply'.
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequest&layout=edit&id=' . $requestId, false));
        } else {
            // Redirect to the list view for 'save'.
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequests', false));
        }
    }

    public function apply($key = null, $urlVar = null)
    {
        return $this->save($key, $urlVar);
    }

    public function getSupplierTemplate()
    {
        $app = Factory::getApplication();
        try {
            if (!Session::checkToken('post')) {
                throw new \Exception('Invalid Token', 403);
            }

            $model = $this->getModel();
            // The model method will get the ID from the application input
            $template = $model->getSupplierTemplate();

            if ($template === false) {
                throw new \Exception('Supplier template not found.', 404);
            }

            // JsonResponse will handle the content type header and wrapping the data
            echo new JsonResponse($template);

        } catch (\Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            $app->setHeader('Status', $code . ' ' . $e->getMessage(), true);
            echo new JsonResponse(null, $e->getMessage(), true);
        }
        $app->close();
    }

    public function sendSupplierMessage()
    {
        $app = Factory::getApplication();
        try {
            if (!Session::checkToken('post')) {
                throw new \Exception(Text::_('JINVALID_TOKEN'), 403);
            }

            $input = $app->input->json;
            $id = $input->get('id', 0, 'int');
            $message = $input->get('supplier_message', '', 'raw');
            $whatsappSent = $input->get('whatsapp_sent', false, 'bool');

            if (!$id) {
                throw new \Exception('Missing required parameter: id.', 400);
            }

            $model = $this->getModel();
            if ($model->sendSupplierMessage($id, $message, $whatsappSent)) {
                echo new JsonResponse(['success' => true, 'message' => Text::_('Message sent to supplier successfully.')]);
            } else {
                throw new \Exception($model->getError() ?: 'An unknown error occurred.', 500);
            }
        } catch (\Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
        $app->close();
    }

    public function upload()
    {
        // Check for request forgeries. The token is sent in the POST body.
        if (!Session::checkToken('post')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            Factory::getApplication()->close();
        }

        $app = Factory::getApplication();
        $input = $app->input;
        $file = $input->files->get('attachment');
        $id = $input->getInt('id');

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            echo new JsonResponse(null, JText::_('COM_BOOKINGMANAGER_ERROR_NO_FILE_UPLOADED'), true);
            $app->close();
        }

        $filename = File::makeSafe($file['name']);
        $filepath = JPATH_ROOT . '/media/com_bookingmanager/attachments/' . $id . '/' . $filename;

        if (!Folder::exists(dirname($filepath))) {
            Folder::create(dirname($filepath));
        }

        if (File::upload($file['tmp_name'], $filepath)) {
            $data = ['filePath' => 'media/com_bookingmanager/attachments/' . $id . '/' . $filename];
            echo new JsonResponse($data);
        } else {
            echo new JsonResponse(null, JText::_('COM_BOOKINGMANAGER_ERROR_FAILED_TO_MOVE_UPLOADED_FILE'), true);
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

    public function deleteAttachment()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        $input = $app->input;

        try {
            if (!Session::checkToken('post')) {
                throw new \Exception('Invalid Token', 403);
            }

            // Admin security check
            if (Factory::getUser()->get('guest')) {
                throw new \Exception('Permission denied.', 403);
            }

            $filePath = $input->getString('filePath');
            if (empty($filePath)) {
                throw new \Exception('File path is required.', 400);
            }

            // Basic security check on file path
            if (strpos($filePath, 'media/com_bookingmanager/attachments/') !== 0) {
                throw new \Exception('Invalid file path.', 400);
            }

            $fullPath = JPATH_ROOT . '/' . $filePath;

            if (File::exists($fullPath)) {
                if (!File::delete($fullPath)) {
                    throw new \Exception('Failed to delete file from filesystem.', 500);
                }
            }

            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__booking_attachments'))
                ->where($db->quoteName('file_path') . ' = ' . $db->quote($filePath));

            $db->setQuery($query);
            $db->execute();

            echo new JsonResponse(['success' => true, 'message' => 'Attachment deleted.']);

        } catch (\Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }

        $app->close();
    }
}