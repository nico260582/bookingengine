<?php
namespace RTHolidays\Component\BookingManager\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class RegionsController extends AdminController
{
    public function getModel($name = 'Region', $prefix = '', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
