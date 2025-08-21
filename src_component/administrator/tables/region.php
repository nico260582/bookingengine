<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class BookingmanagerTableRegion extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_regions', 'id', $db);
    }

    public function check()
    {
        if (empty($this->name)) {
            $this->setError('The name cannot be empty.');
            return false;
        }

        return true;
    }
}
