<?php
namespace RTHolidays\Component\BookingManager\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class SuppliersModel extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('id, name, abbreviation, published')->from($db->quoteName('#__bookingmanager_suppliers'));
        return $query;
    }
}