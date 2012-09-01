<?php

namespace Orkestra\Bundle\GuzzleBundle\Services;


/**
 * Service metadata
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class ServiceMetadata
{
    private $events = array();
    private $name;

    public function __construct($name)
    {
        $this->name;
    }

    public function addEvent($callback, $event)
    {
        $this->events[$event][] = $callback;
        return $this;
    }

    public function hasEvent($event)
    {
        return (isset($this->events[$event])) ? true : false;
    }

    public function setEvents(array $events)
    {
        $this->events = $events;
        return $this;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function __toString()
    {
        return __CLASS__ . '@' . spl_object_hash($this);
    }

    public function __sleep()
    {
        $serialized = array(
            'name',
            'events'
        );

        return $serialized;
    }

}