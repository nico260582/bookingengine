<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerSupplier extends FormController
{
    public function searchProperties()
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $user = Factory::getUser();

        // Security check
        if (!Session::checkToken('get') || !$user->authorise('core.manage', 'com_bookingmanager')) {
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => false, 'message' => Text::_('JERROR_ALERTNOAUTHOR')]);
            $app->close();
        }

        try {
            // Get the model
            $model = AdminModel::getInstance('Supplier', 'BookingmanagerModel');

            // Prepare options
            $options = [
                'currentSupplierId' => $input->getInt('supplier_id', 0),
                'searchTerm'        => $input->getString('search', '')
            ];

            // Get the properties
            $properties = $model->getAllPropertiesWithAssignments($options);

            // Send the JSON response
            $app->setHeader('Content-Type', 'application/json');
            $jsonOutput = json_encode(['success' => true, 'data' => $properties]);

            if ($jsonOutput === false) {
                throw new \Exception('JSON encoding error: ' . json_last_error_msg(), 500);
            }

            echo $jsonOutput;
        } catch (\Exception $e) {
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        $app->close();
    }
}