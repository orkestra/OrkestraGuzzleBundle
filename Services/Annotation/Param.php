<?php

namespace Orkestra\Bundle\GuzzleBundle\Services\Annotation;

/**
 * Annotation class for @Param().
 *
 * @Annotation
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class Param
{
    private $name;
    private $type;
    private $required = false;
    private $location;
    private $default;
    private $doc;
    private $minLength;
    private $maxLength;
    private $static;
    private $prepend;
    private $append;
    private $filters;

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

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setRequired($required)
    {
        $this->required = $required;
    }

    public function getRequired()
    {
        return $this->required;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function getMaxLength()
    {
        return $this->maxLength;
    }

    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;
    }

    public function getMinLength()
    {
        return $this->minLength;
    }

    public function setPrepend($prepend)
    {
        $this->prepend = $prepend;
    }

    public function getPrepend()
    {
        return $this->prepend;
    }

    public function setStatic($static)
    {
        $this->static = $static;
    }

    public function getStatic()
    {
        return $this->static;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setDoc($doc)
    {
        $this->doc = $doc;
    }

    public function getDoc()
    {
        return $this->doc;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setAppend($append)
    {
        $this->append = $append;
    }

    public function getAppend()
    {
        return $this->append;
    }
}
