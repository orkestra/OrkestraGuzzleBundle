<?php
namespace Orkestra\Bundle\GuzzleBundle\Loader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Loader\FileLoader;


/**
 * ServiceFileLoader
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class ServiceFileLoader extends FileLoader
{
    /**
     * Find the service file
     *
     * @param $file
     * @param null $type
     * @return \Symfony\Component\Config\Resource\FileResource
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $this->setCurrentDir(dirname($path));

        return new FileResource($path);
    }

    /**
     * Support only php files
     *
     * @param $resource
     * @param null $type
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
    }
}
