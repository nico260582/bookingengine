<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModPropertysearchHelper
{
    public static function getMainRegions()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('main_region'))
            ->from($db->quoteName('#__bookingmanager_properties'))
            ->where($db->quoteName('main_region') . ' IS NOT NULL')
            ->group($db->quoteName('main_region'))
            ->order($db->quoteName('main_region') . ' ASC');

        $db->setQuery($query);

        return $db->loadColumn();
    }
}
