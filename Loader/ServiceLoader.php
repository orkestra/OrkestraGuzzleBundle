<?php

namespace Orkestra\Bundle\GuzzleBundle\Loader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Orkestra\Bundle\GuzzleBundle\Generator\Dumper\JsonGeneratorDumper;
use Guzzle\Http\Plugin\OauthPlugin;
use Guzzle\Http\Plugin\LogPlugin;
use Guzzle\Common\Log\MonologLogAdapter;
use Guzzle\Service\Client;
use Orkestra\Bundle\GuzzleBundle\Plugin\WsseAuthPlugin;

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
     * @param array                                            $options
     */
    public function __construct(LoaderInterface $loader, Container $container, array $options = array())
    {
        $this->options = $options;
        $this->container = $container;
        $this->loader = $loader;
    }

    /**
     * Load services and write it to a cache file.
     *
     * @param  array                                          $services
     * @return \Orkestra\Bundle\GuzzleBundle\Services\Service
     */
    public function load($options)
    {
        $class = $options['name'].'-GuzzleServiceCache';
        $cache = new ConfigCache($this->options['cache_dir'].'/orkestra_guzzle/'.$class.'.json', true);
        $metaCache = new ConfigCache($this->options['cache_dir'].'/orkestra_guzzle/'.$class.'-Metadata.php', true);

        if (!$cache->isFresh($class)) {
            list($content, $resources, $serviceMeta) = $this->generateService($options['class'], $options['params']);
            //TODO: Add file resource
            $cache->write($content, $resources);

            //todo options for meta cache
            $metaCache->write(serialize($serviceMeta), $resources);
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

        $client = Client::factory($serviceInstance->getConfig());

        $cookiePlugin = new \Guzzle\Http\Plugin\CookiePlugin(new \Guzzle\Http\CookieJar\ArrayCookieJar());
        $client->addSubscriber($cookiePlugin);

        if (isset($options['oauth']) && !empty($options['oauth'])) {
            $oauthPlugin = new OauthPlugin($options['oauth']);
            $client->addSubscriber($oauthPlugin);
        }

        if (isset($options['wsse']) && !empty($options['wsse'])) {
            $wssePlugin = new WsseAuthPlugin($options['wsse']['username'], $options['wsse']['password']);
            $client->addSubscriber($wssePlugin);
        }

        if ($options['logging']) {
            //Container seems pretty expensive, we may just pass the logger through the constructor
            $logPlugin = new LogPlugin(new MonologLogAdapter($this->container->get('logger')));
            $client->addSubscriber($logPlugin);
        }

        $serviceInstance->setClient($client);
        $serviceInstance->setDescription($cache);
        $serviceInstance->setMetadata($metaCache);
        $serviceInstance->bindEvents();
        return $serviceInstance;
    }

    /**
     * Load service's annotations and return a json string
     *
     * @param $class
     * @param  array $params
     * @return array
     */
    public function generateService($class, array $params = array())
    {
        list($commands, $resource, $serviceMeta) = $this->loader->load($class);
        $dumper = new JsonGeneratorDumper();

        return array($dumper->dump($commands), $resource, $serviceMeta);
    }
}
