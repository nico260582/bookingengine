<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

// Setup the component's class loader
JLoader::register('BookingmanagerHelper', __DIR__ . '/helpers/bookingmanager.php');
JLoader::register('BookingmanagerControllerProperties', __DIR__ . '/controllers/properties.php');
JLoader::register('BookingmanagerControllerProperty', __DIR__ . '/controllers/property.php');
JLoader::register('BookingmanagerControllerComplexes', __DIR__ . '/controllers/complexes.php');
JLoader::register('BookingmanagerControllerComplex', __DIR__ . '/controllers/complex.php');
JTable::addIncludePath(__DIR__ . '/tables');

// Get the controller instance.
$controller = BaseController::getInstance('Bookingmanager', ['base_path' => __DIR__]);

// Execute the task
$controller->execute(Factory::getApplication()->input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();