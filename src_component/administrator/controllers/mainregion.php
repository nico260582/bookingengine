<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerMainregion extends AdminController
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getModel($name = 'Mainregion', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
