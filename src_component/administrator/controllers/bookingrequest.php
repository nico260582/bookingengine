<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Response\JsonResponse;

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

        $task = $this->getTask();
        if ($task == 'apply') {
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequest&layout=edit&id=' . $requestId, false));
        } else {
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=bookingrequests', false));
        }
    }

    public function getSupplierTemplate()
    {
        $app = Factory::getApplication();
        try {
            if (!\Joomla\CMS\Session\Session::checkToken('post')) {
                throw new \Exception(\Joomla\CMS\Language\Text::_('JINVALID_TOKEN'), 403);
            }

            $model = $this->getModel('Bookingrequest');
            $template = $model->getSupplierTemplate();

            if ($template === false) {
                throw new \Exception('Supplier template could not be processed.', 500);
            }

            echo new \Joomla\CMS\Response\JsonResponse($template);

        } catch (\Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo new \Joomla\CMS\Response\JsonResponse(null, $e->getMessage(), true);
        }
        $app->close();
    }


    private function _executeSupplierAction(callable $modelCall, $successMessage, $errorMessage)
    {
        $app = Factory::getApplication();
        try {
            if (!\Joomla\CMS\Session\Session::checkToken('post')) {
                throw new \Exception(\Joomla\CMS\Language\Text::_('JINVALID_TOKEN'), 403);
            }

            $model = $this->getModel('Bookingrequest');
            $result = $modelCall($model);

            if ($result) {
                echo new \Joomla\CMS\Response\JsonResponse(['success' => true, 'message' => $successMessage, 'data' => $result]);
            } else {
                throw new \Exception($model->getError() ?: $errorMessage, 500);
            }
        } catch (\Exception $e) {
            $logMessage = sprintf(
                "Supplier Action Error: %s | Input Data: %s",
                $e->getMessage(),
                json_encode(Factory::getApplication()->input->post->getArray())
            );
            \Joomla\CMS\Log\Log::add($logMessage, \Joomla\CMS\Log\Log::ERROR, 'com_bookingmanager');

            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo new \Joomla\CMS\Response\JsonResponse(null, $e->getMessage(), true);
        }
        $app->close();
    }

    public function logWhatsAppMessage()
    {
        $input = Factory::getApplication()->input;
        $id = $input->getInt('id', 0);
        $message = $input->get('supplier_message', '', 'raw');
        $whatsappSent = $input->getBool('whatsapp_sent', false);

        $modelCall = function($model) use ($id, $message, $whatsappSent) {
            return $model->logWhatsAppMessage($id, $message, $whatsappSent);
        };

        $this->_executeSupplierAction($modelCall, 'WhatsApp communication logged successfully.', 'An unknown error occurred while logging WhatsApp message.');
    }

    public function sendSupplierMessage()
    {
        $input = Factory::getApplication()->input;
        $id = $input->getInt('id', 0);
        $message = $input->get('supplier_message', '', 'raw');
        $whatsappSent = $input->getBool('whatsapp_sent', false);
        $attachments = $input->get('attachments', [], 'array');

        $modelCall = function($model) use ($id, $message, $whatsappSent, $attachments) {
            return $model->sendSupplierMessage($id, $message, $whatsappSent, $attachments);
        };

        $this->_executeSupplierAction($modelCall, 'Message sent successfully.', 'An unknown error occurred while sending the message.');
    }

    public function upload()
    {
        $app = Factory::getApplication();
        try {
            if (!\Joomla\CMS\Session\Session::checkToken('post')) {
                throw new \Exception(\Joomla\CMS\Language\Text::_('JINVALID_TOKEN'), 403);
            }

            $input = $app->input;
            $file = $input->files->get('file'); // The JS should send the file under the key 'file'
            $id = $input->getInt('id');

            if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('File upload error: ' . ($file['error'] ?? 'Unknown Error'));
            }

            $targetDir = JPATH_SITE . '/images/booking-attachments/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $fileName = preg_replace('/[^A-Za-z0-9_.-]/', '', basename($file['name']));
            $targetFile = $targetDir . $fileName;

            $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $counter = 1;
            while (file_exists($targetFile)) {
                $fileName = $fileNameWithoutExt . '_' . $counter++ . '.' . $extension;
                $targetFile = $targetDir . $fileName;
            }

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $relativePath = 'images/booking-attachments/' . $fileName;
                echo new \Joomla\CMS\Response\JsonResponse(['success' => true, 'filePath' => $relativePath, 'fileName' => $fileName]);
            } else {
                throw new \Exception('Failed to move uploaded file.');
            }

        } catch (\Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo new \Joomla\CMS\Response\JsonResponse(null, $e->getMessage(), true);
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

    public function deleteSupplierAttachment()
    {
        $app = Factory::getApplication();
        try {
            if (!\Joomla\CMS\Session\Session::checkToken('post')) {
                throw new \Exception(\Joomla\CMS\Language\Text::_('JINVALID_TOKEN'), 403);
            }

            $input = $app->input;
            $filePath = $input->getString('filePath');
            if (empty($filePath)) {
                throw new \Exception('File path is required.', 400);
            }

            $model = $this->getModel('Bookingrequest');
            if ($model->deleteSupplierAttachment($filePath)) {
                echo new \Joomla\CMS\Response\JsonResponse(['success' => true, 'message' => 'Attachment deleted.']);
            } else {
                throw new \Exception($model->getError() ?: 'An unknown error occurred while deleting the attachment.', 500);
            }
        } catch (\Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo new \Joomla\CMS\Response\JsonResponse(null, $e->getMessage(), true);
        }
        $app->close();
    }
}