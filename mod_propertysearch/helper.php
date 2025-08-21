<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModPropertysearchHelper
{
    public static function getMainRegions()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(array('id', 'name')))
            ->from($db->quoteName('#__bookingmanager_regions'))
            ->where($db->quoteName('parent_id') . ' = 0')
            ->where($db->quoteName('state') . ' = 1')
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList();
    }
}
