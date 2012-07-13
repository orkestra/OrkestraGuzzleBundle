<?php

namespace Orkestra\GuzzleBundle;

use Orkestra\GuzzleBundle\Services\Service;

class Guzzle
{
    private $services;
    private $current;

    public function get($service)
    {
        return $this->services[$service];
    }

    public function getServices()
    {
        return $this->services;
    }

    public function setServices($services)
    {
        $this->services = $services;
    }

    public function setService($name, Service $service)
    {
        $this->service[$name] = $service;
    }
}