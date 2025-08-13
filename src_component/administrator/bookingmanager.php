<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

// Get the controller instance. This will automatically route to the correct
// controller based on the 'task' variable (e.g., task=property.add loads PropertyController)
$controller = BaseController::getInstance('Bookingmanager');

// Execute the task
$controller->execute(Factory::getApplication()->input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();