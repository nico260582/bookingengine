<?php
defined('_JEXEC') or die;

class BookingmanagerControllerTemplates extends JControllerAdmin
{
    public function getModel($name = 'Template', $prefix = 'BookingmanagerModel', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}