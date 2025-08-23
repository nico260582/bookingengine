<?php
namespace RTHolidays\Component\BookingManager\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class ComplexTable extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_complexes', 'id', $db);
    }
}
