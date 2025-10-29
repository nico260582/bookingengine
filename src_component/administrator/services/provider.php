<?php
defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->set(
            ComponentDispatcherFactoryInterface::class,
            function (Container $container) {
                $factory = new ComponentDispatcherFactory($container->get('Component'));
                $factory->setNamespace('Bhm\\Component\\Bookingmanager');
                return $factory;
            }
        );

        $container->set(
            MVCFactoryInterface::class,
            function (Container $container) {
                $factory = new MVCFactory($container->get(ComponentDispatcherFactoryInterface::class));
                $factory->setNamespace('Bhm\\Component\\Bookingmanager');
                return $factory;
            }
        );
    }
};
