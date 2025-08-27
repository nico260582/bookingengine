<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

require_once JPATH_SITE . '/modules/mod_propertysearch/helper.php';

class BookingmanagerControllerAjax extends BaseController
{
    public function getRegionAvailability()
    {
        $app = Factory::getApplication();
        $input = $app->input;

        $startDate = $input->getString('start_date');
        $endDate = $input->getString('end_date');
        $guests = $input->getInt('guests', 1);

        // Basic validation
        if (empty($startDate) || empty($endDate)) {
            echo new JsonResponse(null, 'Start and end dates are required.', true);
            $app->close();
        }

        try {
            $data = ModPropertysearchHelper::getAvailablePropertiesByRegion($startDate, $endDate, $guests);
            echo new JsonResponse($data);
        } catch (\Exception $e) {
            echo new JsonResponse(null, $e->getMessage(), true);
        }

        $app->close();
    }
}
