<?php

namespace Orkestra\Bundle\GuzzleBundle;

use Orkestra\Bundle\GuzzleBundle\Services\Service;

/**
 * Class to manage services created.
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class Guzzle
{
    /**
     * @var array
     */
    private $services;

    /**
     * Get service
     *
     * @param $service
     * @return \Orkestra\Bundle\GuzzleBundle\Services\Service
     */
    public function get($service)
    {
        return $this->services[$service];
    }

    /**
     * Get an array of all services
     *
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Set services
     *
     * @param $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * Set a service
     * @param $name
     * @param \Orkestra\Bundle\GuzzleBundle\Services\Service $service
     */
    public function setService($name, \Orkestra\Bundle\GuzzleBundle\Services\Service $service)
    {
        $this->service[$name] = $service;
    }
}