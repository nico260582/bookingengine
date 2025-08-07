<?php
defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Table;

class BookingmanagerTableSupplier extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_suppliers', 'id', $db);
    }

    public function check()
    {
        if (trim($this->name) == '')
        {
            $this->setError('Name is required.');
            return false;
        }

        if (empty($this->alias))
        {
            $this->alias = OutputFilter::stringURLSafe($this->name);
        }

        return true;
    }
}