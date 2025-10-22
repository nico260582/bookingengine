<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class BookingmanagerController extends BaseController
{
    public function display($cachable = false, $urlparams = array())
    {
        $viewName   = $this->input->get('view', 'bookingrequests');
        $viewLayout = $this->input->get('layout', 'default');
        $view = $this->getView($viewName, 'html');
        $view->setLayout($viewLayout);
        $view->display();
    }
}
