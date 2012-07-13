<?php

namespace Orkestra\Bundle\GuzzleBundle\Services;

use Guzzle\Common\Collection;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Service\Exception\ValidationException;

abstract class Service
{
    private $vars = array();
    private $config = array();
    private $results;
    private $headers = array();
    private $client;
    private $description;
    private $response;

    public function __construct(array $vars = array())
    {
        $this->config = $this->prepareConfig($this->vars, $vars);
    }

    protected function prepareConfig(array $config = null, array $defaults = null, array $required = null)
    {
        $collection = new Collection($defaults);

        foreach ((array) $config as $key => $value) {
            $collection->set($key, $value);
        }

        foreach ((array) $required as $key) {
            if ($collection->hasKey($key) === false) {
                throw new ValidationException(
                    "Config must contain a '{$key}' key"
                );
            }
        }

        return $collection;
    }

    public function getConfig()
    {
        return $this->config;
    }

    private function setResults($results)
    {
        $this->results = $results;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
    }

    public function setHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setClient($client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setDescription($description)
    {
        if (!$this->getClient()) {
            throw new \Exception('Client must be configured before setting description.');
        }
        $clientDescription = \Guzzle\Service\Description\ServiceDescription::factory($description);
        $this->getClient()->setDescription($clientDescription);
        //TODO: Add better deserializer
        $this->description = json_decode(file_get_contents($description));
    }

    public function createAuthorizationHeader($string, $values)
    {
        foreach ($values as $key => $value) {
            if ($value != false && !is_null($value)) {
                $string = preg_replace('/{'.$key.'}/', $value, $string);
            }
        }

        if (preg_match('/{.*}/', $string, $matches))
        {
            throw new \Exception('These values are required: '.implode(', ', $matches));
        }

        return $string;
    }

    public function execute($commandName, array $params = array())
    {
        $this->authenticate();

        $this->getClient()->setDefaultHeaders($this->getHeaders());
        $command = $this->getClient()->getCommand($commandName, $params);

        $method = $this->description->commands->$commandName->methodName;

        $this->response = $this->getClient()->execute($command);

        return $this->$method();
    }

    abstract public function authenticate();
}