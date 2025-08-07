<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class BookingmanagerTableBookingrequest extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__booking_requests', 'id', $db);
    }
}