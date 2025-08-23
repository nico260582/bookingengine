<?php
namespace RTHolidays\Component\BookingManager\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class RegionTable extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_regions', 'id', $db);
    }
}
