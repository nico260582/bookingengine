<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class BookingmanagerViewCommunication extends BaseHtmlView
{
    protected $request;
    protected $messages;
    protected $attachments;
    protected $isLoggedIn = false;

    public function display($tpl = null)
    {
        $session = Factory::getSession();
        $requestId = $session->get('bookingmanager_request_id');

        if ($requestId)
        {
            $this->isLoggedIn = true;
            $model = $this->getModel();
            $this->request = $model->getRequestData($requestId);
            $this->messages = $model->getMessages($requestId);
            $this->attachments = $model->getAttachments($requestId);
        }

        parent::display($tpl);
    }
}