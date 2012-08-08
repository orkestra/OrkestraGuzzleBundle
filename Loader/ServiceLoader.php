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

    private $container;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     * @param array $options
     */
    public function __construct(LoaderInterface $loader, \Symfony\Component\DependencyInjection\Container $container, array $options = array())
    {
        $this->options = $options;
        $this->container = $container;
        $this->loader = $loader;
    }

    /**
     * Load services and write it to a cache file.
     *
     * @param array $services
     * @return \Orkestra\Bundle\GuzzleBundle\Services\Service
     */
    public function load($options)
    {
        $class = $options['name'].'-GuzzleServiceCache';
        $cache = new ConfigCache($this->options['cache_dir'].'/orkestra_guzzle/'.$class.'.json', true);

        if (!$cache->isFresh($class)) {
            list($content, $resource) = $this->generateService($options['class'], $options['params']);
            //TODO: Add file resource
            $cache->write($content, array($resource));
        }

        $args = array();
        $args[] = $options['params'];

        if ($options['services']) {
            foreach ($options['services'] as $value) {
                $args[] = $this->container->get($value['id']);
            }
        }

        $options['args'] = $args;

        //todo cache service instance
        $serviceReflection = new \ReflectionClass($options['class']);
        $serviceInstance = $serviceReflection->newInstanceArgs($options['args']);

        $client = \Guzzle\Service\Client::factory($serviceInstance->getConfig());
        $cookiePlugin = new \Guzzle\Http\Plugin\CookiePlugin(new \Guzzle\Http\CookieJar\ArrayCookieJar());
        $client->addSubscriber($cookiePlugin);

        $serviceInstance->setClient($client);
        $serviceInstance->setDescription($cache);

        return $serviceInstance;
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