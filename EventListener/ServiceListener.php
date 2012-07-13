<?php

namespace Orkestra\Bundle\GuzzleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Orkestra\Bundle\GuzzleBundle\Loader\ServiceLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceListener
{
    private $services = array();
    private $serviceLoader;
    private $container;

    public function __construct($services, ServiceLoader $serviceLoader, ContainerInterface $container)
    {
        $this->services = $services;
        $this->serviceLoader = $serviceLoader;
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!empty($this->services)) {
            $this->container->get('guzzle')->setServices($this->serviceLoader->load($this->services));
        }
    }
}
