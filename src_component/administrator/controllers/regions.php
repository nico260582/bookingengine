<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerControllerRegions extends AdminController
{
    public function getModel($name = 'Regions', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
