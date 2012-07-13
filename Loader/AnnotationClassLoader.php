<?php

namespace Orkestra\Bundle\GuzzleBundle\Loader;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Command;
use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Doc;
use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Param;
use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Headers;
use Doctrine\Common\Annotations\AnnotationException;

class AnnotationClassLoader implements LoaderInterface
{
    protected $reader;

    /**
     * Constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $class A class name
     * @param string $type     The resource type
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        $commands = array();

        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if (preg_match('/Command$/', $name)) {
                $annotations = $this->reader->getMethodAnnotations($method);
                foreach ($annotations as $annotation) {
                    $annotationName = explode('\\', get_class($annotation));
                    $commands[$name][array_pop($annotationName)][] = $annotation;
                }
            }
        }

        return $commands;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        // TODO: Implement supports() method.
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolverInterface A LoaderResolverInterface instance
     */
    public function getResolver()
    {
    }

    /**
     * Sets the loader resolver.
     *
     * @param LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
    }
}
