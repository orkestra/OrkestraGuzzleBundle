<?php

namespace Orkestra\Bundle\GuzzleBundle\Loader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Orkestra\Bundle\GuzzleBundle\Generator\Dumper\JsonGeneratorDumper;
use Orkestra\Bundle\GuzzleBundle\Services\ServiceCollection;

/**
 * Class to load and cache services
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class ServiceLoader
{
    /**
     * @var \Symfony\Component\Config\Loader\LoaderInterface
     */
    protected $loader;

    /**
     * @var array
     */
    private $options = array();

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     * @param array $options
     */
    public function __construct(LoaderInterface $loader, array $options = array())
    {
        $this->options = $options;
        $this->loader = $loader;
    }

    /**
     * Load services and write it to a cache file.
     *
     * @param array $services
     * @return \Orkestra\Bundle\GuzzleBundle\Services\ServiceCollection
     */
    public function load(array $services)
    {
        $collection = new ServiceCollection();

        foreach ($services as $key => $service) {

            $class = $key.'-GuzzleServiceCache';
            $cache = new ConfigCache($this->options['cache_dir'].'/orkestra_guzzle/'.$class.'.json', true);
            if (!$cache->isFresh($class)) {
                list($content, $resource) = $this->generateService($service['class'], $service['params']);
                //TODO: Add file resource
                $cache->write($content, array($resource));
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

    /**
     * Load service's annotations and return a json string
     *
     * @param $class
     * @param array $params
     * @return array
     */
    public function generateService($class, array $params = array())
    {
        list($commands, $resource) = $this->loader->load($class);
        $dumper = new JsonGeneratorDumper();

        return array($dumper->dump($commands), $resource);
    }
}