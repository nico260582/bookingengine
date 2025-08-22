<?php
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Psr\Container\ContainerInterface;

return new class implements ComponentInterface
{
    public function getContainer(ContainerInterface $parent): ContainerInterface
    {
        $parent->registerServiceProvider(new MVCFactory($this->getNamespace()));
        $parent->registerServiceProvider(new ComponentDispatcherFactory($this->getNamespace()));

        return $parent;
    }

    public function dispatch(MVCFactoryInterface $factory): void
    {
        $dispatcher = $factory->createDispatcher();

        // Access check.
        if (!ComponentHelper::getParams('com_bookingmanager')->get('enabled', true)) {
            throw new \Exception('Component disabled', 404);
        }

        $dispatcher->dispatch();
    }

    protected function getNamespace(): string
    {
        return 'Rtholidays\\Component\\Bookingmanager';
    }
};