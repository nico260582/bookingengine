<?php

defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface, BootableExtensionInterface
{
    public function register(Container $container)
    {
        $container->registerServiceProvider(new MVCFactory('\\Rtholidays\\Component\\Bookingmanager'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Rtholidays\\Component\\Bookingmanager'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new MVCComponent($container->get(\Joomla\CMS\Dispatcher\DispatcherInterface::class));
                return $component;
            }
        );
    }

    public function boot(Container $container)
    {
    }
};