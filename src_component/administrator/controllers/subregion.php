<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerControllerSubregion extends AdminController
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getModel($name = 'Subregion', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
