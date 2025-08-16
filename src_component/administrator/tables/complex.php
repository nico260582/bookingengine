<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class BookingmanagerTableComplex extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_complexes', 'id', $db);
    }
}
