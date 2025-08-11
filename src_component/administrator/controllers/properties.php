<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerProperties extends BaseController
{
    public function get()
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $user = Factory::getUser();

        // 1. Check token and permissions
        if (!Session::checkToken('get') || !$user->authorise('core.manage', 'com_bookingmanager')) {
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => false, 'message' => Text::_('JERROR_ALERTNOAUTHOR')]);
            $app->close();
        }

        try {
            // 2. Get the model
            $model = AdminModel::getInstance('Supplier', 'BookingmanagerModel');

            // 3. Prepare options for the model method
            $options = [
                'currentSupplierId' => $input->getInt('supplier_id', 0),
                'searchTerm'        => $input->getString('search', '')
            ];

            // 4. Get the properties
            $properties = $model->getAllPropertiesWithAssignments($options);

            // 5. Send the JSON response
            $app->setHeader('Content-Type', 'application/json');
            $jsonOutput = json_encode(['success' => true, 'data' => $properties]);

            if ($jsonOutput === false) {
                throw new \Exception('JSON encoding error: ' . json_last_error_msg(), 500);
            }

            echo $jsonOutput;
        } catch (\Exception $e) {
            // 6. Catch potential errors
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        $app->close();
    }
}
