<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Router\Route;

class BookingmanagerController extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        $view  = $input->getCmd('view', 'communication');

        // Allow 'terms' view to be displayed publicly
        if ($view !== 'terms') {
            $input->set('view', 'communication');
        }

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
                'status'         => 'New',
                'client_ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'client_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            if (empty($data['client_email']) || empty($data['property_name']) || empty($data['start_date'])) {
                throw new Exception('Required data is missing.', 400);
            }

            // User creation logic
            $userId = (int) \Joomla\CMS\User\UserHelper::getUserId($data['client_email']);
            if (!$userId) {
                $user = Factory::getUser(0);
                $password = \Joomla\CMS\User\UserHelper::genRandomPassword(10);
                $userData = [
                    'name'      => $data['client_name'],
                    'username'  => $data['client_email'],
                    'password'  => $password,
                    'password2' => $password,
                    'email'     => $data['client_email'],
                    'groups'    => [2] // Registered
                ];
                if (!$user->bind($userData) || !$user->save()) {
                    throw new Exception('User creation failed: ' . $user->getError());
                }
                $userId = $user->id;
                $data['new_user_password'] = $password;
            }
            $data['user_id'] = $userId;

            $articleId = $input->post->getInt('article_id', 0);
            $supplierAbbreviation = 'GEN'; // General fallback
            if ($articleId) {
                $db = Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select('s.abbreviation')
                    ->from($db->quoteName('#__bookingmanager_suppliers', 's'))
                    ->join('INNER', $db->quoteName('#__bookingmanager_property_map', 'm') . ' ON s.id = m.supplier_id')
                    ->where('m.property_id = ' . (int)$articleId);
                $abbreviation = $db->setQuery($query)->loadResult();
                if ($abbreviation) {
                    $supplierAbbreviation = $abbreviation;
                }
            }

            $data['booking_ref'] = 'BHM-' . $supplierAbbreviation . '-' . date('dmy') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
            $data['pin'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            $table = JTable::getInstance('Bookingrequest', 'BookingmanagerTable');
            if (!$table->bind($data) || !$table->store()) {
                 throw new Exception('Database save error: ' . $table->getError());
            }
            
            if (!empty($data['client_message'])) {
                $commTable = JTable::getInstance('Communication', 'BookingmanagerTable');
                $commData = [ 'request_id' => $table->id, 'created_at' => $data['created_at'], 'author' => $data['client_name'] . ' (Client)', 'message' => $data['client_message'] ];
                $commTable->save($commData);
            }

            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
            BookingmanagerHelper::sendNotificationEmails($table->id, 'all', '', $data['new_user_password'] ?? '');
            
            $this->logClientActivity($table->id, $userId, 'Booking Created', 'Initial submission from booking form.');

            echo json_encode(['success' => true, 'bookingRef' => $data['booking_ref']]);
        } catch (\Throwable $t) {
            Log::add('Booking form submission failed: ' . $t->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($t->getCode() >= 400 && $t->getCode() < 600) ? $t->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $t->getMessage()]);
        }
        $app->close();
    }

    public function logTermsAndConditions()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        try {
            if (!Session::checkToken('post')) { throw new Exception('Invalid Token', 403); }
            $input = $app->input;
            $bookingRef = $input->getString('booking_ref', '');
            if (empty($bookingRef)) {
                throw new Exception('Booking reference is required.', 400);
            }

            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('b.id, p.article_id')
                ->from($db->quoteName('#__booking_requests', 'b'))
                ->join('LEFT', $db->quoteName('#__content', 'p') . ' ON b.property_name = p.title')
                ->where('b.booking_ref = ' . $db->quote($bookingRef));
            $booking = $db->setQuery($query)->loadObject();

            if (!$booking) {
                throw new Exception('Booking not found.', 404);
            }

            $query->clear()
                ->select('s.terms_and_conditions')
                ->from($db->quoteName('#__bookingmanager_suppliers', 's'))
                ->join('INNER', $db->quoteName('#__bookingmanager_property_map', 'm') . ' ON s.id = m.supplier_id')
                ->where('m.property_id = ' . (int)$booking->article_id);
            $terms = $db->setQuery($query)->loadResult();

            if (!empty($terms)) {
                $termsLog = new \stdClass();
                $termsLog->booking_id = $booking->id;
                $termsLog->terms_content = $terms;
                $termsLog->created_at = (new Date('now'))->toSql();

                $db->insertObject('#__bookingmanager_terms_log', $termsLog, 'id');
                $termsLogId = $termsLog->id;

                $bookingTable = JTable::getInstance('Bookingrequest', 'BookingmanagerTable');
                $bookingTable->load($booking->id);
                $bookingTable->terms_log_id = $termsLogId;
                if (!$bookingTable->store()) {
                    throw new Exception('Failed to update booking with terms log ID.');
                }
            }
            echo json_encode(['success' => true]);
        } catch (\Throwable $t) {
            Log::add('T&C logging failed: ' . $t->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($t->getCode() >= 400 && $t->getCode() < 600) ? $t->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $t->getMessage()]);
        }
        $app->close();
    }

    public function updateBookingFromPortal()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        $input = $app->input;
        try {
            if (!Session::checkToken('post')) { throw new Exception('Invalid Token', 403); }

            $bookingId = $input->post->getInt('booking_id', 0);
            if (!$bookingId) { throw new Exception('Booking ID is required.', 400); }

            if (!$this->_isAllowedToAccessBooking($bookingId)) {
                throw new Exception('Permission Denied. You do not have access to this booking.', 403);
            }

            JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_bookingmanager/tables');
            $table = JTable::getInstance('Bookingrequest', 'BookingmanagerTable');
            if (!$table->load($bookingId)) {
                throw new Exception('Booking request not found.', 404);
            }

            $userId = Factory::getUser()->id;
            $oldAdults = $table->adults;
            $newAdults = $input->post->getInt('adults', $table->adults);
            $oldChildren = $table->children;
            $newChildren = $input->post->getInt('children', $table->children);
            $oldChildAges = $table->child_ages;
            $newChildAges = $input->post->getString('child_ages', '');

            $oldStartDate = $table->start_date;
            $newStartDate = $input->post->getString('start_date', $table->start_date);
            $oldEndDate = $table->end_date;
            $newEndDate = $input->post->getString('end_date', $table->end_date);

            $table->adults = $newAdults;
            $table->children = $newChildren;
            $table->child_ages = $newChildAges;
            $table->start_date = $newStartDate;
            $table->end_date = $newEndDate;
            $table->price_estimate = $input->post->getString('price_estimate', $table->price_estimate);
            $table->unit_count = $input->post->getInt('unit_count', $table->unit_count);

            if (!$table->store()) {
                throw new Exception('Failed to save booking changes: ' . $table->getError());
            }

            $changes = [];
            if ($oldAdults != $newAdults) { $changes['Adults'] = ['old' => $oldAdults, 'new' => $newAdults]; }
            if ($oldChildren != $newChildren) { $changes['Children'] = ['old' => $oldChildren, 'new' => $newChildren]; }
            if ($oldChildAges != $newChildAges) { $changes['Child Ages'] = ['old' => $oldChildAges, 'new' => $newChildAges]; }
            if ($oldStartDate != $newStartDate) { $changes['Start Date'] = ['old' => $oldStartDate, 'new' => $newStartDate]; }
            if ($oldEndDate != $newEndDate) { $changes['End Date'] = ['old' => $oldEndDate, 'new' => $newEndDate]; }

            if (!empty($changes)) {
                $logDetails = [];
                foreach ($changes as $field => $value) {
                    $logDetails[] = "{$field}: {$value['old']} -> {$value['new']}";
                }
                $this->logClientActivity($bookingId, $userId, 'Booking Modified', implode(', ', $logDetails));

                JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
                BookingmanagerHelper::sendNotificationEmails($bookingId, 'email_admin_booking_modified', '', '', [], $changes);
            }

            echo json_encode(['success' => true, 'message' => 'Your request has been updated and the administrator has been notified.']);

        } catch (\Throwable $t) {
            Log::add('updateBookingFromPortal failed: ' . $t->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($t->getCode() >= 400 && $t->getCode() < 600) ? $t->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $t->getMessage()]);
        }
        $app->close();
    }

    public function getPricingForRequest()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        try {
            $bookingId = $app->input->getInt('booking_id', 0);
            if (!$bookingId) {
                throw new Exception('Booking ID is required.', 400);
            }

            if (!$this->_isAllowedToAccessBooking($bookingId)) {
                throw new Exception('Permission Denied. You do not have access to this booking.', 403);
            }

            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('p.id')
                ->from($db->quoteName('#__content', 'p'))
                ->join('INNER', $db->quoteName('#__booking_requests', 'r') . ' ON p.title = r.property_name COLLATE utf8mb4_unicode_ci')
                ->where('r.id = ' . (int)$bookingId);
            $articleId = $db->setQuery($query)->loadResult();

            if (!$articleId) {
                throw new Exception('Could not find associated property.', 404);
            }

            JLoader::register('ModBookingFormHelper', JPATH_SITE . '/modules/mod_bookingform/helper.php');
            $pricingRules = ModBookingFormHelper::getPricingDataForArticle($articleId);

            echo json_encode(['success' => true, 'pricingRules' => $pricingRules]);

        } catch (\Throwable $t) {
            Log::add('getPricingForRequest failed: ' . $t->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($t->getCode() >= 400 && $t->getCode() < 600) ? $t->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $t->getMessage()]);
        }
        $app->close();
    }

    public function upload()
    {
        if (!Session::checkToken('post')) {
            echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('JINVALID_TOKEN'), true);
            Factory::getApplication()->close();
            return;
        }

        $app = Factory::getApplication();
        $input = $app->input;
        $file = $input->files->get('attachment');
        $requestId = $input->getInt('request_id');

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('COM_BOOKINGMANAGER_ERROR_NO_FILE_UPLOADED'), true);
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
            echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('COM_BOOKINGMANAGER_ERROR_FAILED_TO_MOVE_UPLOADED_FILE'), true);
        }

        $app->close();
    }

    public function addClientMessage()
    {
        if (!Session::checkToken('post')) {
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=communication', false), JText::_('JINVALID_TOKEN'), 'error');
            return;
        }

        $app = Factory::getApplication();
        $input = $app->input;
        $message = $input->getString('message');
        $attachments = json_decode($input->get('uploaded_attachments', '[]', 'raw'), true);
        $requestId = $app->getSession()->get('bookingmanager_request_id');

        $model = $this->getModel('Communication', 'BookingmanagerModel');
        if ($model->saveClientMessage($requestId, $message, $attachments)) {
            if (!empty($message) || !empty($attachments)) {
                JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
                $notificationMessage = !empty($message) ? $message : 'A new file has been uploaded by the client.';
                $attachmentData = [];
                if (!empty($attachments)) {
                    foreach ($attachments as $filePath) {
                        $attachmentData[] = ['name' => basename($filePath)];
                    }
                }
                BookingmanagerHelper::sendNotificationEmails($requestId, 'email_admin_client_reply', $notificationMessage, '', $attachmentData);
            }
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=communication', false), 'Message sent.');
        } else {
            $this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=communication', false), 'Error sending message.', 'error');
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

            $filePath = $input->getString('filePath');
            $requestId = $input->getInt('request_id');
            $sessionRequestId = $app->getSession()->get('bookingmanager_request_id');

            // Security check: Ensure the request ID from the client matches the one in their session
            if (!$requestId || $requestId !== $sessionRequestId) {
                throw new \Exception('Permission denied.', 403);
            }

            if (empty($filePath)) {
                throw new \Exception('File path is required.', 400);
            }

            // Basic security check on file path
            if (strpos($filePath, 'media/com_bookingmanager/attachments/' . $requestId) !== 0) {
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
                ->where($db->quoteName('file_path') . ' = ' . $db->quote($filePath))
                ->where($db->quoteName('request_id') . ' = ' . (int)$requestId);

            $db->setQuery($query);
            $db->execute();

            echo new \Joomla\CMS\Response\JsonResponse(['success' => true, 'message' => 'Attachment deleted.']);

        } catch (\Throwable $t) {
            $code = ($t->getCode() >= 400 && $t->getCode() < 600) ? $t->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo new \Joomla\CMS\Response\JsonResponse(['success' => false, 'message' => $t->getMessage()]);
        }

        $app->close();
    }

    private function logClientActivity($bookingId, $userId, $actionType, $actionDetails = '', $screenSize = '')
    {
        if (empty($bookingId)) {
            Log::add('Attempted to log client activity with an empty booking ID.', Log::WARNING, 'com_bookingmanager');
            return; // Do not proceed if the booking ID is invalid
        }

        $db = Factory::getDbo();
        $log = new \stdClass();
        $log->booking_request_id = $bookingId;
        $log->created_at  = (new Date('now'))->toSql();
        $log->user_id     = $userId;
        $log->ip_address  = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $log->user_agent  = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $log->screen_size = $screenSize;
        $log->action_type = $actionType;
        $log->action_details = $actionDetails;

        try {
            $db->insertObject('#__booking_client_activity_logs', $log);
        } catch (\Throwable $t) {
            Log::add('Failed to log client activity: ' . $t->getMessage(), Log::ERROR, 'com_bookingmanager');
        }
    }

    public function logActivity()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        $input = $app->input;

        try {
            if (!Session::checkToken('post')) { throw new Exception('Invalid Token', 403); }

            $bookingId = $input->post->getInt('booking_id', 0);
            $actionType = $input->post->getString('action_type', 'Viewed Portal');
            $actionDetails = $input->post->getString('action_details', '');
            $screenSize = $input->post->getString('screen_size', '');

            $userId = Factory::getUser()->id;

            if ($bookingId) {
                $this->logClientActivity($bookingId, $userId, $actionType, $actionDetails, $screenSize);
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Booking ID is required.', 400);
            }

        } catch (\Throwable $t) {
            Log::add('logActivity failed: ' . $t->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($t->getCode() >= 400 && $t->getCode() < 600) ? $t->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $t->getMessage()]);
        }
        $app->close();
    }

    public function validateCoupon()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        $input = $app->input;

        try {
            if (!Session::checkToken()) { throw new Exception('Invalid Token', 403); }
            $couponCode = $input->getString('coupon_code', '');
            $articleId  = $input->getInt('article_id', 0);

            if (empty($couponCode) || !$articleId) {
                throw new Exception('Coupon code and article ID are required.', 400);
            }

            JLoader::register('ModBookingFormHelper', JPATH_SITE . '/modules/mod_bookingform/helper.php');
            $pricingRules = ModBookingFormHelper::getPricingDataForArticle($articleId);

            if (!$pricingRules || !isset($pricingRules['coupon_codes'])) {
                throw new Exception('No pricing rules found for this property.', 404);
            }

            $couponData = null;
            foreach ($pricingRules['coupon_codes'] as $coupon) {
                if (strcasecmp($coupon['code'], $couponCode) === 0) {
                    $couponData = $coupon;
                    break;
                }
            }

            if (!$couponData) {
                throw new Exception('Invalid coupon code.', 404);
            }

            $isPermanent = $couponData['is_permanent'] ?? '0';
            if ($isPermanent !== '1') {
                $today = new Date('now');
                $startDate = !empty($couponData['start_date']) ? new Date($couponData['start_date']) : null;
                $endDate = !empty($couponData['end_date']) ? new Date($couponData['end_date']) : null;

                if (($startDate && $today < $startDate) || ($endDate && $today > $endDate)) {
                    throw new Exception('This coupon is not active at this time.', 400);
                }
            }

            $discountPercent = (float)($couponData['discount_percent'] ?? 0);

            echo json_encode([
                'success'  => true,
                'discount' => $discountPercent,
                'message'  => "Success! A {$discountPercent}% discount has been applied."
            ]);

        } catch (\Throwable $t) {
            $code = ($t->getCode() >= 400 && $t->getCode() < 600) ? $t->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $t->getMessage()]);
        }
        $app->close();
    }

    private function _isAllowedToAccessBooking($bookingId)
    {
        $user = Factory::getUser();
        if ($user->guest) {
            return false; // Not logged in
        }

        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_bookingmanager/tables');
        $booking = JTable::getInstance('Bookingrequest', 'BookingmanagerTable');
        if (!$booking->load($bookingId)) {
            return false; // Booking does not exist
        }

        // Allow access if the user is the owner of the booking
        if ($booking->user_id == $user->id) {
            // Check if the PIN stored in the session matches the booking's PIN
            $sessionPin = Factory::getApplication()->getSession()->get('bookingmanager_pin');
            if ($sessionPin === $booking->pin) {
                return true;
            }
        }

        // Allow access for administrators/super users
        if ($user->authorise('core.admin', 'com_bookingmanager')) {
            return true;
        }

        return false;
    }
}