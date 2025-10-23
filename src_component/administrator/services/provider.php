<?php
namespace Bhm\Component\Bookingmanager\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Bhm\\Component\\Bookingmanager'));
        $container->registerServiceProvider(new MVCFactory('\\Bhm\\Component\\Bookingmanager'));
        $container->registerServiceProvider(new RouterFactory('\\Bhm\\Component\\Bookingmanager'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new \Bhm\Component\Bookingmanager\Administrator\Extension\BookingmanagerComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};
