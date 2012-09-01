<?php

namespace Orkestra\Bundle\GuzzleBundle\Services;

use Orkestra\Bundle\GuzzleBundle\Services\Service;

/**
 * Service Collection
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class ServiceCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var array
     */
    private $services;

    /**
     * Constructor.
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
     * @param string  $name    The service name
     * @param Service $service A Service instance
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

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->services[] = $value;
        } else {
            $this->services[$offset] = $value;
        }
    }

    /**
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->services[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->services[$offset]);
    }

    /**
     * @param  mixed      $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->services[$offset]) ? $this->services[$offset] : null;
    }
}
