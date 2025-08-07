<?php
defined('_JEXEC') or die;

class BookingmanagerControllerBookingrequests extends JControllerAdmin
{
    public function getModel($name = 'Bookingrequest', $prefix = 'BookingmanagerModel', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}