<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\User\UserHelper;

class BookingmanagerController extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        $view  = $input->getCmd('view', 'communication'); // Default to communication view
        $input->set('view', 'communication');

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
            if (!$table->save($data)) { throw new Exception('Database save error: ' . $table->getError()); }
            
            if (!empty($data['client_message'])) {
                $commTable = JTable::getInstance('Communication', 'BookingmanagerTable');
                $commData = [ 'request_id' => $table->id, 'created_at' => $data['created_at'], 'author' => $data['client_name'] . ' (Client)', 'message' => $data['client_message'] ];
                $commTable->save($commData);
            }

            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
            BookingmanagerHelper::sendNotificationEmails($table->id, 'all', '', $data['new_user_password'] ?? '');
            
            $this->logClientActivity($table->id, $userId, 'Booking Created', 'Initial submission from booking form.');

            echo json_encode(['success' => true, 'bookingRef' => $data['booking_ref']]);
        } catch (Exception $e) {
            Log::add('Booking form submission failed: ' . $e->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
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

            // Security check would be needed here to ensure user owns this booking

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

            $oldStartDate = $table->start_date;
            $newStartDate = $input->post->getString('start_date', $table->start_date);
            $oldEndDate = $table->end_date;
            $newEndDate = $input->post->getString('end_date', $table->end_date);

            $table->adults = $newAdults;
            $table->children = $newChildren;
            $table->start_date = $newStartDate;
            $table->end_date = $newEndDate;
            $table->price_estimate = $input->post->getString('price_estimate', $table->price_estimate);
            $table->unit_count = $input->post->getInt('unit_count', $table->unit_count);

            if (!$table->store()) {
                throw new Exception('Failed to save booking changes: ' . $table->getError());
            }

            $details = [];
            if ($oldAdults != $newAdults) { $details[] = "Adults: {$oldAdults} -> {$newAdults}"; }
            if ($oldChildren != $newChildren) { $details[] = "Children: {$oldChildren} -> {$newChildren}"; }
            if ($oldStartDate != $newStartDate) { $details[] = "Start Date: {$oldStartDate} -> {$newStartDate}"; }
            if ($oldEndDate != $newEndDate) { $details[] = "End Date: {$oldEndDate} -> {$newEndDate}"; }

            if (!empty($details)) {
                $this->logClientActivity($bookingId, $userId, 'Booking Modified', implode(', ', $details));
            }

            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
            BookingmanagerHelper::sendNotificationEmails($bookingId, 'admin_client_update');

            echo json_encode(['success' => true, 'message' => 'Your request has been updated.']);

        } catch (Exception $e) {
            Log::add('updateBookingFromPortal failed: ' . $e->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
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

            // A more robust security check should be implemented here, e.g., checking session PIN

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

        } catch (Exception $e) {
            Log::add('getPricingForRequest failed: ' . $e->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
        $app->close();
    }

    private function logClientActivity($bookingId, $userId, $actionType, $actionDetails = '', $screenSize = '')
    {
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
        } catch (Exception $e) {
            Log::add('Failed to log client activity: ' . $e->getMessage(), Log::ERROR, 'com_bookingmanager');
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

        } catch (Exception $e) {
            Log::add('logActivity failed: ' . $e->getMessage(), Log::ERROR, 'com_bookingmanager');
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
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

        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            if (!headers_sent()) { http_response_code($code); }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        $app->close();
    }
}