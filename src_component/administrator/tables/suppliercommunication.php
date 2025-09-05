<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class BookingmanagerTableSupplierCommunication extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__booking_supplier_communication', 'id', $db);
    }
}
