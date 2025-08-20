<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_bookingmanager'))
{
	throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Register the helper
JLoader::register('BookingmanagerHelper', __DIR__ . '/helpers/bookingmanager.php');

// Load component CSS
$doc = Factory::getDocument();
$doc->addStyleSheet('components/com_bookingmanager/assets/css/bookingmanager.css');
$doc->addStyleSheet('components/com_bookingmanager/assets/css/custom-booking-styles.css');

// Get an instance of the controller prefixed by Bookingmanager
$controller = BaseController::getInstance('Bookingmanager');

// Perform the Request task
$controller->execute(Factory::getApplication()->input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();