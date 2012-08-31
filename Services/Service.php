<?php

namespace Orkestra\Bundle\GuzzleBundle\Services;

use Guzzle\Common\Collection;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Service\Exception\ValidationException;
use Guzzle\Http\Plugin\AsyncPlugin;

use Orkestra\Bundle\GuzzleBundle\DataMapper\PropertyPathMapper;

/**
 * Service base class
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
abstract class Service
{
    /**
     * @var array
     */
    private $vars = array();

    /**
     * @var array|\Guzzle\Common\Collection
     */
    private $config = array();

    /**
     * @var mixed
     */
    private $results;

    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var \Guzzle\Service\Client
     */
    private $client;

    /**
     * @var object
     */
    private $description;

    /**
     * @var mixed
     */
    private $response;

    /**
     * @var mixed
     */
    private $mapper;

    /**
     * Constructor.
     *
     * @param array $vars
     */
    public function __construct(array $vars = array())
    {
        $this->config = $this->prepareConfig($this->vars, $vars);
    }

    /**
     * Prepare the config for Guzzle's client
     *
     * @param array|null $config
     * @param array|null $defaults
     * @param array|null $required
     * @return \Guzzle\Common\Collection
     * @throws \Guzzle\Service\Exception\ValidationException
     */
    protected function prepareConfig(array $config = null, array $defaults = null, array $required = null)
    {
        $collection = new Collection($defaults);

        foreach ((array) $config as $key => $value) {
            $collection->set($key, $value);
        }

        foreach ((array) $required as $key) {
            if ($collection->hasKey($key) === false) {
                throw new ValidationException("Config must contain a '{$key}' key");
            }
        }

        return $collection;
    }

    /**
     * Get config
     *
     * @return array|\Guzzle\Common\Collection
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set results
     *
     * @param $results
     */
    private function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * Get results
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Set header
     *
     * @param $header
     * @param $value
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
    }

    /**
     * Set multiple headers
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * Get headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set client
     *
     * @param \Guzzle\Service\Client $client
     */
    public function setClient(\Guzzle\Service\Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get client
     *
     * @return \Guzzle\Service\Client
     */
    public function getClient()
    {
        return $this->client;
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

    /**
     * Set service's and guzzle's description
     *
     * @param $description
     * @throws \Exception
     */
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

    /**
     * Method for creating authorization headers that may
     * have variables that were computed
     *
     * @param $string
     * @param $values
     * @return string
     * @throws \Exception
     */
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

    /**
     * Execute commands
     *
     * @param $commandName
     * @param array $params
     * @return mixed
     */
    public function execute($commandName, array $params = array())
    {
        $this->beforeExecute();

        $client = $this->getClient();

        $client->setDefaultHeaders($this->getHeaders());

        $reference = $this->getReference($commandName);

        if ($this->isAsync($commandName)) {
            $client->addSubscriber(new AsyncPlugin());
        }

        $command = $client->getCommand($commandName, $params);

        $this->response = $client->execute($command);

        $parts = explode(':', $reference);

        if (get_class($this) === $parts[0]) {
            return $this->$parts[1]();
        }

        $refl = new \ReflectionClass($parts[0]);
        $instance = $refl->newInstance();

        return $instance->$parts[1]();
    }

    private function getReference($commandName)
    {
        return $this->description->commands->$commandName->reference;
    }

    private function isAsync($commandName)
    {
        return (isset($this->description->commands->$commandName->async))
            ? $this->description->commands->$commandName->async : false;
    }

    public function bind($object, $data)
    {
        if (!$this->mapper) {
            $this->mapper = new PropertyPathMapper();
        }

        $this->mapper->bind($object, $data);
    }

    /**
     * @abstract
     * @return mixed
     */
    abstract public function beforeExecute();
}