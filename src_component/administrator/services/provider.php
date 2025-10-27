<?php
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Bhm\Component\Bookingmanager\Administrator\Extension\BookingmanagerComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface {
    public function register(Container $container)
    {
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new BookingmanagerComponent($container->get('ComponentDispatcherFactory'));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                return $component;
            }
        );

        $container->registerServiceProvider(new MVCFactory('\\Bhm\\Component\\Bookingmanager', ['legacy' => true]));
    }
};
