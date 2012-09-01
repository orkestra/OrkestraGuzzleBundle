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
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
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

    /**
     * Set Name
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set method
     *
     * @param $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set uri
     *
     * @param $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get uri
     *
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }
}
