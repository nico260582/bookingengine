<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class BookingmanagerTableCommunication extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__booking_communication', 'id', $db);
    }
}