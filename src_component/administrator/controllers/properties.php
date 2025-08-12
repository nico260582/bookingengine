<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerProperties extends BaseController
{
    public function get()
    {
        $app = Factory::getApplication();

        // Security check is still important
        if (!Session::checkToken('get') || !Factory::getUser()->authorise('core.manage', 'com_bookingmanager')) {
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => false, 'message' => Text::_('JERROR_ALERTNOAUTHOR')]);
            $app->close();
        }

        // --- DEBUGGING ---
        // Return a fixed, simple response to test the entire AJAX connection.
        $dummyProperties = [
            '1' => (object)['id' => '1', 'title' => 'Test Property A (from debug)', 'assignment' => null],
            '2' => (object)['id' => '2', 'title' => 'Test Property B (from debug)', 'assignment' => null],
        ];

        $app->setHeader('Content-Type', 'application/json');
        echo json_encode(['success' => true, 'data' => $dummyProperties]);
        $app->close();
    }
}
