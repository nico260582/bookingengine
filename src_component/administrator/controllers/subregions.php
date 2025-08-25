<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerSubregions extends AdminController
{
    public function getModel($name = 'Subregion', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function getSubRegions()
    {
        $app = Factory::getApplication();
        $mainRegionId = $app->input->getInt('main_region_id', 0);

        if (!$mainRegionId) {
            // Use echo for a consistent JSON structure on error
            echo new JsonResponse(['success' => false, 'message' => Text::_('COM_BOOKINGMANAGER_ERROR_NO_MAIN_REGION_ID')]);
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
            // Wrap the response in the structure the JS expects
            echo new JsonResponse(['success' => true, 'data' => $subRegions]);
        } catch (\Exception $e) {
            echo new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
        $app->close();
    }
}
