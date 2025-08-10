<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class BookingmanagerViewCommunication extends BaseHtmlView
{
    protected $request;
    protected $messages;
    protected $attachments;
    protected $isLoggedIn = false;

    public function display($tpl = null)
    {
        $session = Factory::getSession();
        $user    = Factory::getUser();
        $model   = $this->getModel();

        $this->isLoggedIn = false;
        $this->request = null;
        $this->messages = [];
        $this->attachments = [];
        $this->userRequests = [];

        // Priority 1: Check for a PIN-based session
        $requestId = $session->get('bookingmanager_request_id');
        if ($requestId) {
            $this->isLoggedIn = true;
            $this->request = $model->getRequestData($requestId);
            $this->messages = $model->getMessages($requestId);
            $this->attachments = $model->getAttachments($requestId);
        }

        // Priority 2: Check if a Joomla user is logged in
        if (!$user->guest) {
            $this->isLoggedIn = true; // A Joomla user is always "logged in" to the portal
            $this->userRequests = $model->getRequestsForUser($user->id);

            // If there's no active PIN session, but the user has requests,
            // make the most recent one the "active" request for display.
            if (!$this->request && !empty($this->userRequests)) {
                $this->request = $this->userRequests[0];
                $this->messages = $model->getMessages($this->request->id);
                $this->attachments = $model->getAttachments($this->request->id);
            }
        }

        $doc = Factory::getDocument();
        $doc->addScript('https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js');
        $doc->addScript(JUri::root(true) . '/modules/mod_bookingform/media/js/portal.js?v=' . filemtime(JPATH_SITE . '/modules/mod_bookingform/media/js/portal.js'));

        if ($this->request) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)->select('attribs')->from($db->quoteName('#__content'))->where('title = ' . $db->quote($this->request->property_name));
            $params = new \Joomla\Registry\Registry($db->setQuery($query)->loadResult());

            $options = [
                'booking_id' => $this->request->id,
                'baseUrl'    => Uri::root(true) . '/',
                'token'      => Session::getFormToken(),
                'start_date' => $this->request->start_date,
                'end_date'   => $this->request->end_date,
                'currencySymbol' => '€', // This should probably be a global setting
                'totalAccommodationGuests' => $params->get('total_accommodation_guests', 2)
            ];
            $doc->addScriptOptions('com_bookingmanager', $options);
        }

        parent::display($tpl);
    }
}