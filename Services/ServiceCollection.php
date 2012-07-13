<?php

namespace Orkestra\Bundle\GuzzleBundle\Services;

use Symfony\Component\Config\Resource\ResourceInterface;
use Orkestra\Bundle\GuzzleBundle\Services\Service;

class ServiceCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    private $services;

    /**
     * Constructor.
     *
     * @api
     */
    public function __construct()
    {
        $this->services = array();
    }

    /**
     * Gets the current ServiceCollection as an Iterator that includes all services
     *
     * @return \ArrayIterator An \ArrayIterator interface
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->services);
    }

    /**
     * Gets the number of Services in this collection.
     *
     * @return int The number of services in this collection
     */
    public function count()
    {
        return count($this->services);
    }

    /**
     * Adds a service.
     *
     * @param string $name  The service name
     * @param Service  $service A Service instance
     *
     * @throws \InvalidArgumentException When service name contains non valid characters
     *
     * @api
     */
    public function add($name, Service $service)
    {
        $this->remove($name);

        $this->services[$name] = $service;
    }

    /**
     * Returns all services in this collection.
     *
     * @return array An array of services
     */
    public function all()
    {
        return $this->services;
    }

    /**
     * Gets a service by name
     *
     * @param string $name The service name
     *
     * @return Service|null A Service instance or null when not found
     */
    public function get($name)
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }
        return null;
    }

    /**
     * Removes a service by name
     *
     * @param string|array $name The service name or an array of service names
     */
    public function remove($name)
    {
        unset($this->services[$name]);
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->services[] = $value;
        } else {
            $this->services[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->services[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->services[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->services[$offset]) ? $this->services[$offset] : null;
    }
}