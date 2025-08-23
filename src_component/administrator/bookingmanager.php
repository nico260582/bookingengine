<?php

defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

return new class implements ServiceProviderInterface, BootableExtensionInterface
{
    public function register(Container $container)
    {
        \Joomla\CMS\Loader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');

        $container->registerServiceProvider(new MVCFactory());
        $container->registerServiceProvider(new ComponentDispatcherFactory());

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new MVCComponent($container->get(\Joomla\CMS\Dispatcher\DispatcherInterface::class));
                return $component;
            }
        );
    }

    public function boot(ContainerInterface $container)
    {
    }
};