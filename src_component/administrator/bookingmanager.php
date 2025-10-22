<?php
defined('_JEXEC') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_bookingmanager'))
{
	throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Get the component instance
$component = Factory::getContainer()->get(ComponentInterface::class);

// Get the dispatcher and dispatch the request
$dispatcher = Factory::getContainer()
    ->get(ComponentDispatcherFactoryInterface::class)
    ->createDispatcher($component);

$dispatcher->dispatch();
