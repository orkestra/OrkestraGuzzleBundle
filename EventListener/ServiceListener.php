<?php

namespace Orkestra\Bundle\GuzzleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Orkestra\Bundle\GuzzleBundle\Loader\ServiceLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service Listener to load services on request
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class ServiceListener
{
    /**
     * @var array
     */
    private $services = array();

    /**
     * @var \Orkestra\Bundle\GuzzleBundle\Loader\ServiceLoader
     */
    private $serviceLoader;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param array                                                     $services
     * @param ServiceLoader                                             $serviceLoader
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(array $services, ServiceLoader $serviceLoader, ContainerInterface $container)
    {
        $this->services = $services;
        $this->serviceLoader = $serviceLoader;
        $this->container = $container;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!empty($this->services)) {
            $services = array();
            foreach ($this->services as $key => $service) {
                $args = array();
                $args[] = $service['params'];

                if ($service['services']) {
                    foreach ($service['services'] as $value) {
                        $args[] = $this->container->get($value['id']);
                    }
                }

                $service['args'] = $args;
                $services[$key] = $service;
            }

            $this->container->get('guzzle')->setServices($this->serviceLoader->load($services));
        }
    }
}
