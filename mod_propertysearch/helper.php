<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModPropertysearchHelper
{
    public static function getMainRegions()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select(array($db->quoteName('id'), $db->quoteName('name')))
            ->from($db->quoteName('#__bookingmanager_main_regions'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList();
    }
}
