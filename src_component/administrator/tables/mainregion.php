<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class BookingmanagerTableMainregion extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_main_regions', 'id', $db);
    }
}
