<?php
defined('_JEXEC') or die;

class BookingmanagerTableComplex extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_complexes', 'id', $db);
    }

}
