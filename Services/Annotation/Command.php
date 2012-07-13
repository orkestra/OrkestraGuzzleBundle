<?php

namespace Orkestra\Bundle\GuzzleBundle\Services\Annotation;

/**
 * Annotation class for @Command().
 *
 * @Annotation
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class Command
{
    private $name;
    private $method;
    private $uri;

    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters.
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.$key;
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf("Unknown property '%s' on annotation '%s'.", $key, get_class($this)));
            }
            $this->$method($value);
        }
    }


    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }
}
