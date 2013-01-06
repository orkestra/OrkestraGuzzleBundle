<?php

namespace Orkestra\Bundle\GuzzleBundle\Services;

use Orkestra\Bundle\GuzzleBundle\DataMapper\PropertyPathMapper;

/**
 * Command base class
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
abstract class Command
{
    protected $response;
    protected $mapper;

    public function __construct($response)
    {
        $this->response = $response;
    }
    public function bind($object, $data)
    {
        if (!$this->mapper) {
            $this->mapper = new PropertyPathMapper();
        }

        $this->mapper->bind($object, $data);
    }

    /**
     * Get response
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
