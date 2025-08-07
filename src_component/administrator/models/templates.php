<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class BookingmanagerModelTemplates extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*')->from($db->quoteName('#__bookingmanager_templates'));
        return $query;
    }
}