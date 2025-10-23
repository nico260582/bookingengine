<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerController extends AdminController
{
    public function display($cachable = false, $urlparams = array())
    {
        $viewName = $this->input->get('view', 'bookingrequests');
        $this->input->set('view', $viewName);

        return parent::display($cachable, $urlparams);
    }
}
