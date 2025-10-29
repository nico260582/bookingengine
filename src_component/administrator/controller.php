<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class BookingmanagerController extends BaseController
{
    protected $default_view = 'bookingrequests';

    public function display($cachable = false, $urlparams = array())
    {
        $this->input->set('view', $this->input->getCmd('view', $this->default_view));
        parent::display($cachable, $urlparams);
        return $this;
    }
}