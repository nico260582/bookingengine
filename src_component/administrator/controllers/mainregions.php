<?php
defined('_JEXEC') or die;

class BookingmanagerControllerMainregions extends JControllerAdmin
{
    public function getModel($name = 'Mainregion', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
