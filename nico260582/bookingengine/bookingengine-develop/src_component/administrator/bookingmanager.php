<?php
defined('_JEXEC') or die;

// Include the component controller
$controller = JControllerLegacy::getInstance('Bookingmanager');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
