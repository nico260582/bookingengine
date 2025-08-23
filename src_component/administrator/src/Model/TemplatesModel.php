<?php
namespace RTHolidays\Component\BookingManager\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class TemplatesModel extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*')->from($db->quoteName('#__bookingmanager_templates'));
        return $query;
    }
}