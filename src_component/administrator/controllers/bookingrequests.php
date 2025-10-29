<?php
defined('_JEXEC') or die;

class BookingmanagerControllerBookingrequests extends JControllerAdmin
{
    public function getModel($name = 'Bookingrequests', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
