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
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $type;
    
    /**
     * @var bool
     */
    private $required = false;
    
    /**
     * @var string
     */
    private $location;
    
    /**
     * @var string
     */
    private $default;
    
    /**
     * @var string
     */
    private $doc;
    
    /**
     * @var string
     */
    private $minLength;
    
    /**
     * @var string
     */
    private $maxLength;
    
    /**
     * @var string
     */
    private $static;
    
    /**
     * @var string
     */
    private $prepend;
    
    /**
     * @var string
     */
    private $append;
    
    /**
     * @var string
     */
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


    /**
     * Set name
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
     * Set type
     *
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set required
     *
     * @param $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * Get required
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set location
     *
     * @param $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set max length
     *
     * @param $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * Get max length
     *
     * @return string
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set min length
     *
     * @param $minLength
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;
    }

    /**
     * Get min length
     *
     * @return string
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set prepend
     *
     * @param $prepend
     */
    public function setPrepend($prepend)
    {
        $this->prepend = $prepend;
    }

    /**
     * Get prepend
     *
     * @return string
     */
    public function getPrepend()
    {
        return $this->prepend;
    }

    /**
     * Set static
     *
     * @param $static
     */
    public function setStatic($static)
    {
        $this->static = $static;
    }

    /**
     * Get static
     *
     * @return string
     */
    public function getStatic()
    {
        return $this->static;
    }

    /**
     * Set filters
     *
     * @param $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get filters
     *
     * @return string
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set doc
     *
     * @param $doc
     */
    public function setDoc($doc)
    {
        $this->doc = $doc;
    }

    /**
     * Get doc
     *
     * @return string
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * Set default
     *
     * @param $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * Get default
     *
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Set append
     *
     * @param $append
     */
    public function setAppend($append)
    {
        $this->append = $append;
    }

    /**
     * Get append
     *
     * @return string
     */
    public function getAppend()
    {
        return $this->append;
    }
}
