<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\User;

class BookingmanagerController extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = false)
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        $view  = $input->getCmd('view', 'communication'); // Default to communication view
        $input->set('view', $view);

        parent::display($cachable, $urlparams);
        return $this;
    }

    public function submitBooking()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        try {
            if (!Session::checkToken('post')) { throw new Exception('Invalid Token', 403); }
            JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_bookingmanager/tables');
            $input = $app->input;
            $data = [
                'property_name'  => $input->post->get('accommodation', '', 'string'),
                'accommodation_url' => $input->post->get('accommodation_url', '', 'string'),
                'client_name'    => $input->post->get('first_name', '', 'string') . ' ' . $input->post->get('last_name', '', 'string'),
                'client_email'   => $input->post->get('email', '', 'string'),
                'client_phone'   => $input->post->get('telephone', '', 'string'),
                'client_country' => $input->post->get('country', '', 'string'),
                'client_message' => $input->post->get('message', '', 'string'),
                'start_date'     => $input->post->get('start_date', '', 'string'),
                'end_date'       => $input->post->get('end_date', '', 'string'),
                'adults'         => $input->post->get('guest_count', 1, 'int'),
                'children'       => $input->post->get('children_count', 0, 'int'),
                'child_ages'     => implode(', ', $input->post->get('child_ages', [], 'array')),
                'price_estimate' => $input->post->get('price_estimate', 'N/A', 'string'),
                'unit_count'     => $input->post->get('unit_count', 1, 'int'),
                'discount_note'  => $input->post->get('discount_note', '', 'string'),
                'created_at'     => (new Date('now'))->toSql(),
                'status'         => 'New'
            ];
            if (empty($data['client_email']) || empty($data['property_name']) || empty($data['start_date'])) {
                throw new Exception('Required data is missing.', 400);
            }

            // --- New User Creation Logic (Corrected) ---
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('email') . ' = ' . $db->quote($data['client_email']));
            $userId = $db->setQuery($query)->loadResult();

            if (!$userId) {
                $juser = new User;
                $newUserData = [
                    'name' => $data['client_name'],
                    'username' => $data['client_email'],
                    'email' => $data['client_email'],
                    'password' => UserHelper::genRandomPassword(),
                    'groups' => [2] // Registered user group
                ];
                if (!$juser->bind($newUserData) || !$juser->save()) {
                    Log::add('Failed to create new Joomla user for ' . $data['client_email'] . ': ' . $juser->getError(), Log::WARNING, 'com_bookingmanager');
                }
            }
            // --- End New User Creation Logic ---

            $data['booking_ref'] = 'BHM-' . date('dmy') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
            $data['pin'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            $table = JTable::getInstance('Bookingrequest', 'BookingmanagerTable');
            if (!$table->save($data)) { throw new Exception('Database save error: ' . $table->getError()); }
            
            if (!empty($data['client_message'])) {
                $commTable = JTable::getInstance('Communication', 'BookingmanagerTable');
                $commData = [ 'request_id' => $table->id, 'created_at' => $data['created_at'], 'author' => $data['client_name'] . ' (Client)', 'message' => $data['client_message'] ];
                $commTable->save($commData);
            }

            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
            BookingmanagerHelper::sendNotificationEmails($table->id);
            
            echo json_encode(['success' => true, 'bookingRef' => $data['booking_ref']]);
        } catch (Exception $e) {
            Log::add('Booking form submission failed: ' . $e->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
        $app->close();
    }
    
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
        Factory::getApplication()->redirect(Route::_('index.php?option=com_bookingmanager&view=communication', false));
    }
    
    public function submitBooking()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        try {
            if (!Session::checkToken('post')) { throw new Exception('Invalid Token', 403); }
            JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_bookingmanager/tables');
            $input = $app->input;
            $data = [
                'property_name'  => $input->post->get('accommodation', '', 'string'),
                'accommodation_url' => $input->post->get('accommodation_url', '', 'string'),
                'client_name'    => $input->post->get('first_name', '', 'string') . ' ' . $input->post->get('last_name', '', 'string'),
                'client_email'   => $input->post->get('email', '', 'string'),
                'client_phone'   => $input->post->get('telephone', '', 'string'),
                'client_country' => $input->post->get('country', '', 'string'),
                'client_message' => $input->post->get('message', '', 'string'),
                'start_date'     => $input->post->get('start_date', '', 'string'),
                'end_date'       => $input->post->get('end_date', '', 'string'),
                'adults'         => $input->post->get('guest_count', 1, 'int'),
                'children'       => $input->post->get('children_count', 0, 'int'),
                'child_ages'     => implode(', ', $input->post->get('child_ages', [], 'array')),
                'price_estimate' => $input->post->get('price_estimate', 'N/A', 'string'),
                'unit_count'     => $input->post->get('unit_count', 1, 'int'),
                'discount_note'  => $input->post->get('discount_note', '', 'string'),
                'created_at'     => (new Date('now'))->toSql(),
                'status'         => 'New'
            ];
            $articleId = $input->post->getInt('article_id', 0); // Get the article ID from the form

            if (empty($data['client_email']) || empty($data['property_name']) || empty($data['start_date'])) {
                throw new Exception('Required data is missing.', 400);
            }

            // --- New User Creation Logic ---
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('email') . ' = ' . $db->quote($data['client_email']));
            $userId = $db->setQuery($query)->loadResult();

            if (!$userId) {
                $juser = new User;
                $newUserData = [
                    'name' => $data['client_name'],
                    'username' => $data['client_email'],
                    'email' => $data['client_email'],
                    'password' => UserHelper::genRandomPassword(),
                    'groups' => [2] // Registered user group
                ];
                if (!$juser->bind($newUserData) || !$juser->save()) {
                    Log::add('Failed to create new Joomla user for ' . $data['client_email'] . ': ' . $juser->getError(), Log::WARNING, 'com_bookingmanager');
                }
            }
            
            // --- New Booking Reference Logic ---
            $supplierAbbreviation = 'GEN'; // Default abbreviation
            if ($articleId > 0) {
                $query->clear()
                    ->select('s.abbreviation')
                    ->from($db->quoteName('#__bookingmanager_property_map', 'm'))
                    ->join('INNER', $db->quoteName('#__bookingmanager_suppliers', 's') . ' ON m.supplier_id = s.id')
                    ->where('m.property_id = ' . (int)$articleId);
                $abbreviation = $db->setQuery($query)->loadResult();
                if ($abbreviation) {
                    $supplierAbbreviation = $abbreviation;
                }
            }
            $data['booking_ref'] = 'BHM-' . $supplierAbbreviation . '-' . date('dmy') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
            // --- End Booking Reference Logic ---

            $data['pin'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            $table = JTable::getInstance('Bookingrequest', 'BookingmanagerTable');
            if (!$table->save($data)) { throw new Exception('Database save error: ' . $table->getError()); }
            
            if (!empty($data['client_message'])) {
                $commTable = JTable::getInstance('Communication', 'BookingmanagerTable');
                $commData = [ 'request_id' => $table->id, 'created_at' => $data['created_at'], 'author' => $data['client_name'] . ' (Client)', 'message' => $data['client_message'] ];
                $commTable->save($commData);
            }

            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
            BookingmanagerHelper::sendNotificationEmails($table->id);
            
            echo json_encode(['success' => true, 'bookingRef' => $data['booking_ref']]);
        } catch (Exception $e) {
            Log::add('Booking form submission failed: ' . $e->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
        $app->close();
    }
    
    private function uploadAttachment($requestId, $file, $uploaderName)
    {
        if (!$requestId || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $app = Factory::getApplication();
        $filename = File::makeSafe($file['name']);
        $dest_path = JPATH_SITE . '/media/com_bookingmanager/attachments/' . $requestId;

        if (!Folder::exists($dest_path)) {
            Folder::create($dest_path);
        }
        
        $dest_file = $dest_path . '/' . $filename;

        if (File::upload($file['tmp_name'], $dest_file)) {
            $db = Factory::getDbo();
            $attachment = new stdClass();
            $attachment->request_id = (int) $requestId;
            $attachment->file_name = $filename;
            $attachment->file_path = 'media/com_bookingmanager/attachments/' . $requestId . '/' . $filename;
            $attachment->uploaded_by = $uploaderName;
            $attachment->created_at = (new Date('now'))->toSql();
            
            return $db->insertObject('#__booking_attachments', $attachment);
        } else {
            $app->enqueueMessage('File upload failed.', 'error');
            return false;
        }
    }
}