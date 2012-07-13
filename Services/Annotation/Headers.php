<?php

namespace Orkestra\Bundle\GuzzleBundle\Services\Annotation;

/**
 * Annotation class for @Headers().
 *
 * @Annotation
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class Headers
{
    private $headers;

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


    public function setHeaders($headers)
    {
        $this->headers = is_array($headers) ? $headers : array($headers);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setValue($headers)
    {
        $this->setHeaders($headers);
    }

}
