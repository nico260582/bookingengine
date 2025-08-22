<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class BookingmanagerController extends BaseController
{
    public function display($cachable = false, $urlparams = array())
    {
        // Set the default view name
        $this->default_view = 'regions';
        parent::display($cachable, $urlparams);
    }
}
