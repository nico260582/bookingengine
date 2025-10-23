<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerControllerMainregions extends AdminController
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getModel($name = 'Mainregion', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
