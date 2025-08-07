<?php
defined('_JEXEC') or die;

// Ensure the helper is loaded
JLoader::register('BookingmanagerHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

$controller = JControllerLegacy::getInstance('Bookingmanager');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();