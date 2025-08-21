<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class BookingmanagerTableRegion extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_regions', 'id', $db);
    }
}
