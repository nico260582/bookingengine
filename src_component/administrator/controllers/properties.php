<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

use Joomla\CMS\Factory;
class BookingmanagerControllerProperties extends AdminController
{
    public function getModel($name = 'Property', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function getSubRegions()
    {
        $app = Factory::getApplication();
        $parentId = $app->input->getInt('parent_id');
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('id', 'name')))
            ->from($db->quoteName('#__bookingmanager_regions'))
            ->where($db->quoteName('parent_id') . ' = ' . (int) $parentId)
            ->order('name ASC');

        $db->setQuery($query);
        $subRegions = $db->loadObjectList();

        // Use a more compatible way to return JSON
        echo json_encode($subRegions);
        $app->close();
    }
}
