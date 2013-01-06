<?php

namespace Orkestra\Bundle\GuzzleBundle\Services;

use Guzzle\Common\Collection;
use Guzzle\Service\Exception\ValidationException;
use Guzzle\Batch\BatchCommandTransfer;
use Guzzle\Plugin\Async\AsyncPlugin;
use Guzzle\Plugin\Oauth\OauthPlugin;
use Orkestra\Bundle\GuzzleBundle\Plugin\WsseAuthPlugin;
use Orkestra\Bundle\GuzzleBundle\DataMapper\PropertyPathMapper;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var mixed
     */
    private $oauth = false;

    /**
     * @var mixed
     */
    private $wsse = false;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param array $vars
     */
    public function __construct(array $vars = array())
    {
        $this->config = $this->prepareConfig($this->vars, $vars);
        $this->eventDispatcher = new EventDispatcher();
    }

    /**
     * Prepare the config for Guzzle's client
     *
     * @param  array|null                                    $config
     * @param  array|null                                    $defaults
     * @param  array|null                                    $required
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

    public function setMetadata($metadata)
    {
        $this->metadata = unserialize(file_get_contents($metadata));
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function bindEvents()
    {
        $events = $this->metadata->getEvents();
        if ($this->metadata instanceof ServiceMetadata && !empty($events)) {
            foreach ($events as $event => $callbacks) {
                foreach ($callbacks as $reference) {
                    $parts = explode(':', $reference);

                    if (get_class($this) === $parts[0]) {
                        $instance = $this;
                    } else {
                        $refl = new \ReflectionClass($parts[0]);
                        $instance = $refl->newInstance();
                    }

                    $this->eventDispatcher->addListener($event, array($instance, $parts[1]));
                }
            }
        }
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

        if (preg_match('/{.*}/', $string, $matches)) {
            throw new \Exception('These values are required: '.implode(', ', $matches));
        }

        return $string;
    }

    /**
     * Execute commands
     *
     * @param $commandName
     * @param  array $params
     * @return mixed
     */
    public function execute($name, array $params = array())
    {
        $this->eventDispatcher->dispatch('BeforeSend');

        $client = $this->getCommandClient($name);

        $command = $client->getCommand($name, $params);

        $response = $client->execute($command);

        if (is_object($response)) {
            throw new \Exception('Unexpected content: '.(string) $response);
        }

        return $this->executeResponse($name, $response);
    }

    private function getReference($name)
    {
        return $this->description->operations->$name->reference;
    }

    private function getCommandClient($name)
    {
        $client = $this->getClient();

        $client->setDefaultHeaders($this->getHeaders());

        if ($this->isAsync($name)) {
            $client->addSubscriber(new AsyncPlugin());
        }

        if ($this->oauth != false) {
            $client->addSubscriber($this->oauth);
        }

        if ($this->wsse != false) {
            $client->addSubscriber($this->wsse);
        }

        return $client;
    }

    private function isAsync($name)
    {
        return (isset($this->description->commands->$name->async))
            ? $this->description->commands->$name->async : false;
    }

    public function signedOauthRequest($consumerKey, $consumerSecret, $token, $tokenSecret)
    {
        $this->oauth = new OauthPlugin(array(
            'consumer_key'    => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'token'           => $token,
            'token_secret'    => $tokenSecret
        ));
    }

    public function wsseRequest($username, $password, $digester = null, $noncer = null)
    {
        $this->wsse = new WsseAuthPlugin($username, $password, $digester = null, $noncer = null);
    }

    public function bind($object, $data)
    {
        if (!$this->mapper) {
            $this->mapper = new PropertyPathMapper();
        }

        $this->mapper->bind($object, $data);
    }

    public function executeResponse($name, $response)
    {
        $this->response = $response;

        $reference = $this->getReference($name);

        $parts = explode(':', $reference);

        if (get_class($this) === $parts[0]) {
            return $this->$parts[1]();
        }

        $refl = new \ReflectionClass($parts[0]);
        $instance = $refl->newInstanceArgs(array($this->getResponse()));

        return $instance->$parts[1]();
    }

    public function batch(array $batch = array(), $size = 50)
    {
        $queue = new \SplQueue();
        $client = $this->getClient();
        $responses = array();

        $client->getEventDispatcher()->addListener('command.after_send', function($a) use (&$responses) {
            $responses[][$a['command']->getName()] = $a['command']->getResult();
        });

        $this->eventDispatcher->dispatch('BeforeSend');

        foreach ($batch as $command) {

            if (!isset($command[1])) {
                $command[1] = array();
            }

            $c = $this->getCommandClient($command[0])->getCommand($command[0], $command[1]);
            $queue[] = $c;
        }

        $batchTransfer = new BatchCommandTransfer($size);
        $batches = $batchTransfer->createBatches($queue);

        foreach ($batches as $b) {
            $batchTransfer->transfer($b);
        }

        $results = array();

        foreach ($responses as $response) {
            $results[] = $this->executeResponse(key($response), $response[key($response)]);
        }

        return $results;
    }
}
