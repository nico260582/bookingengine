<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\BaseController;

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
                'status'         => 'New'
            ];
            if (empty($data['client_email']) || empty($data['property_name']) || empty($data['start_date'])) {
                throw new Exception('Required data is missing.', 400);
            }
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
}