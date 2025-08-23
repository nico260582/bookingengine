<?php
namespace RTHolidays\Component\BookingManager\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class CommunicationTable extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__booking_communication', 'id', $db);
    }
}