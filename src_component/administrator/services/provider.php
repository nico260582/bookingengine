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
            MVCFactoryInterface::class,
            function (Container $container) {
                $component = $container->get(ComponentInterface::class);
                return new MVCFactory($component);
            }
        );
        $container->set(
            ComponentDispatcherFactoryInterface::class,
            function (Container $container) {
                $component = $container->get(ComponentInterface::class);
                return new ComponentDispatcherFactory($component);
            }
        );
    }
};
