<?php

namespace Orkestra\GuzzleBundle\Loader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Orkestra\GuzzleBundle\Generator\Dumper\JsonGeneratorDumper;
use Orkestra\GuzzleBundle\Services\ServiceCollection;

class ServiceLoader
{
    protected $loader;
    private $options = array();

    public function __construct(LoaderInterface $loader, array $options = array())
    {
        $this->options = $options;
        $this->loader = $loader;
    }

    public function load(array $services)
    {
        $collection = new ServiceCollection();

        foreach ($services as $key => $service) {

            $class = $key.'-GuzzleServiceCache';
            $cache = new ConfigCache($this->options['cache_dir'].'/Orkestra_guzzle/'.$class.'.json', false);
            if (!$cache->isFresh($class)) {
                $content = $this->generateService($service['class'], $service['params']);
                //TODO: Add file resource
                $cache->write($content);
            }

            $serviceInstance = new $service['class']($service['params']);

            //TODO:Create bridge
            $client = \Guzzle\Service\Client::factory($serviceInstance->getConfig());
            $cookiePlugin = new \Guzzle\Http\Plugin\CookiePlugin(new \Guzzle\Http\CookieJar\ArrayCookieJar());
            $client->addSubscriber($cookiePlugin);

            $serviceInstance->setClient($client);
            $serviceInstance->setDescription($cache);

            $collection->add($key, $serviceInstance);
        }

        return $collection;
    }

    public function generateService($class, array $params = array())
    {
        $commands = $this->loader->load($class);
        $dumper = new JsonGeneratorDumper();

        return $dumper->dump($commands);
    }
}