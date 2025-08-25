<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerSubregions extends AdminController
{
    public function getSubRegions()
    {
        $app = Factory::getApplication();
        $mainRegionId = $app->input->getInt('main_region_id', 0);

        if (!$mainRegionId) {
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => false, 'message' => Text::_('COM_BOOKINGMANAGER_ERROR_NO_MAIN_REGION_ID')]);
            $app->close();
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'name']))
            ->from($db->quoteName('#__bookingmanager_sub_regions'))
            ->where($db->quoteName('main_region_id') . ' = ' . (int) $mainRegionId)
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name'));

        $db->setQuery($query);

        try {
            $subRegions = $db->loadObjectList();
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => true, 'data' => $subRegions]);
        } catch (\Exception $e) {
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        $app->close();
    }
}
