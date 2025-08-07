<?php
defined('_JEXEC') or die;
$controller = JControllerLegacy::getInstance('Bookingmanager');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();