<?php
namespace RTHolidays\Component\BookingManager\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Table;

class SupplierTable extends Table
{
    public $out_of_season_surcharge = 10.00;
    public $global_discount = 0.00;
    public $show_global_discount_notification = 1;

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