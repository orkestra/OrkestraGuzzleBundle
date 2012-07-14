<?php

namespace Orkestra\Bundle\GuzzleBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Orkestra\Bundle\GuzzleBundle\Loader\ServiceLoader;

/**
 * Generates the service files.
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class ServiceCacheWarmer extends CacheWarmer
{
    protected $services;
    protected $serviceLoader;

    /**
     * Constructor.
     *
     * @param array $services
     * @param ServiceLoader $serviceLoader
     */
    public function __construct(array $services, ServiceLoader $serviceLoader)
    {
        $this->services = $services;
        $this->serviceLoader = $serviceLoader;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $this->serviceLoader->load($this->services);
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return Boolean always true
     */
    public function isOptional()
    {
        return true;
    }
}