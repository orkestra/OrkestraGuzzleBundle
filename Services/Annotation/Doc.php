<?php

namespace Orkestra\Bundle\GuzzleBundle\Services\Annotation;

/**
 * Annotation class for @Doc().
 *
 * @Annotation
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class Doc
{
    /**
     * @var string
     */
    private $doc;

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
     * @return mixed
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * Set doc
     *
     * @param $doc
     */
    public function setValue($doc)
    {
        $this->setDoc($doc);
    }

}
