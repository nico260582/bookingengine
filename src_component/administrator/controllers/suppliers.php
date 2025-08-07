<?php
defined('_JEXEC') or die;

class BookingmanagerControllerSuppliers extends JControllerAdmin
{
    public function getModel($name = 'Supplier', $prefix = 'BookingmanagerModel', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}