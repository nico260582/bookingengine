<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\LegacyController;
use Joomla\CMS\Language\Text;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_bookingmanager'))
{
	throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Get an instance of the controller prefixed by Bookingmanager
$controller = LegacyController::getInstance('Bookingmanager');

// Perform the Request task
$controller->execute(Factory::getApplication()->input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();