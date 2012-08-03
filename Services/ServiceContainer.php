<?php

namespace Orkestra\Bundle\GuzzleBundle\Services;

use Orkestra\Bundle\GuzzleBundle\Loader\ServiceLoader;

class ServiceContainer
{
    private $collection;
    private $services;
    private $serviceLoader;

    public function __construct(ServiceLoader $loader)
    {
        $this->serviceLoader = $loader;
        $this->collection = new ServiceCollection();
    }

    public function addService(array $options)
    {
        $this->services[$options['name']] = $options;
    }

    public function getService($name) {
        if (isset($this->collection[$name])) {
            return $this->collection[$name];
        }

        if (!isset($this->services[$name])) {
            //todo return exception
            return false;
        }

        $service = $this->serviceLoader->load($this->services[$name]);

        if (($service instanceof Service) === false) {
            //todo return exception
            return false;
        }

        $this->collection->add($name, $service);

        return $service;
    }
}