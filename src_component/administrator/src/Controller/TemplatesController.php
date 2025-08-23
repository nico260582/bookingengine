<?php
namespace RTHolidays\Component\BookingManager\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class TemplatesController extends AdminController
{
    public function getModel($name = 'Template', $prefix = '', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}