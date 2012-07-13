<?php
namespace Orkestra\Bundle\GuzzleBundle\Loader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Loader\FileLoader;


class ServiceFileLoader extends FileLoader
{
    public function load($file, $type = null)
    {
        // the loader variable is exposed to the included file below
        $loader = $this;

        $path = $this->locator->locate($file);
        $this->setCurrentDir(dirname($path));

        return new FileResource($path);
    }
    
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
    }
}
