<?php
defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherInterface;
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

    public function getDispatcher(MVCFactoryInterface $factory): DispatcherInterface
    {
        return $factory->createDispatcher();
    }

    protected function getNamespace(): string
    {
        return 'Rtholidays\\Component\\Bookingmanager';
    }
};