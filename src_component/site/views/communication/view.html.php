<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;

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
        $doc->addScript(JUri::root(true) . '/modules/mod_bookingform/media/js/portal.js?v=' . filemtime(JPATH_SITE . '/modules/mod_bookingform/media/js/portal.js'));

        parent::display($tpl);
    }
}