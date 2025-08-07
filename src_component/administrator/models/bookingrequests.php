<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class BookingmanagerModelBookingrequests extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*')->from($db->quoteName('#__booking_requests'))->order('created_at DESC');
        return $query;
    }
}