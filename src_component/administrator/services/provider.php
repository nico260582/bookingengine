<?php
namespace Rtholidays\Component\Bookingmanager\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Rtholidays\Component\Bookingmanager\Administrator\Extension\BookingmanagerComponent;

return new class implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new BookingmanagerComponent($container->get('ComponentDispatcherFactory'));

                // Set the component's namespace
                $component->setNamespace('Rtholidays\Component\Bookingmanager');

                return $component;
            }
        );

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
