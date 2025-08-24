<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class BookingmanagerTableSubregion extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_sub_regions', 'id', $db);
    }
}
