<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class BookingmanagerControllerProperties extends BaseController
{
    public function get()
    {
        $app = Factory::getApplication();
        $input = $app->input;

        // Get the model
        $model = AdminModel::getInstance('Supplier', 'BookingmanagerModel');

        // Prepare options for the model method
        $options = [
            'currentSupplierId' => $input->getInt('supplier_id', 0),
            'searchTerm'        => $input->getString('search', '')
        ];

        // Get the properties
        $properties = $model->getAllPropertiesWithAssignments($options);

        // Send the JSON response
        $app->setHeader('Content-Type', 'application/json');
        echo json_encode(['success' => true, 'data' => $properties]);
        $app->close();
    }
}
