<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewTerms extends HtmlView
{
    protected $terms_content;
    protected $booking_ref;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $ref = $input->getString('ref', '');

        if (empty($ref)) {
            $app->enqueueMessage('Booking reference is missing.', 'error');
            return false;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('l.terms_content, r.booking_ref')
            ->from($db->quoteName('#__bookingmanager_terms_log', 'l'))
            ->join('INNER', $db->quoteName('#__booking_requests', 'r') . ' ON l.booking_request_id = r.id')
            ->where($db->quoteName('r.booking_ref') . ' = ' . $db->quote($ref));

        $result = $db->setQuery($query)->loadObject();

        if ($result) {
            $this->terms_content = $result->terms_content;
            $this->booking_ref = $result->booking_ref;
        } else {
            $app->enqueueMessage('Could not find the terms for the specified booking.', 'error');
            return false;
        }

        parent::display($tpl);
    }
}
