<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModPropertysearchHelper
{
    public static function getRegions()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('region'))
            ->from($db->quoteName('#__bookingmanager_properties'))
            ->where($db->quoteName('region') . ' IS NOT NULL')
            ->group($db->quoteName('region'))
            ->order($db->quoteName('region') . ' ASC');

        $db->setQuery($query);

        return $db->loadColumn();
    }
}
