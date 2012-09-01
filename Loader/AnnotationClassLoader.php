<?php

namespace Orkestra\Bundle\GuzzleBundle\Loader;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Command;
use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Param;

/**
 * Loader class to load annotations from php service files
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class AnnotationClassLoader implements LoaderInterface
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $reader;

    /**
     * @var ServiceFileLoader
     */
    protected $loader;

    /**
     * Constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader, ServiceFileLoader $loader)
    {
        $this->reader = $reader;
        $this->loader = $loader;
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $class A class name
     * @param string $type  The resource type
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $baseClass = new \ReflectionClass($class);

        $commands = array();
        $commandReflections = array($baseClass);

        $commandDirectory = realpath(dirname($baseClass->getFileName())).'/Command';

        $resources = array($this->loader->load($baseClass->getFileName()));

        if (is_dir($commandDirectory)) {
            $files = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($commandDirectory), \RecursiveIteratorIterator::LEAVES_ONLY));
            usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
                return (string) $a > (string) $b ? 1 : -1;
            });

            foreach ($files as $file) {
                if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                    continue;
                }

                if ($class = $this->findClass($file)) {
                    $refl = new \ReflectionClass($class);
                    if ($refl->isAbstract()) {
                        continue;
                    }

                    $resources[] = $this->loader->load($refl->getFileName());
                    $commandReflections[] = $refl;
                }
            }
        }

        foreach ($commandReflections as $class) {
            foreach ($class->getMethods() as $method) {
                $methodName = $method->getName();
                $name = $class->getName().':'.$methodName;
                if (preg_match('/Command$/', $methodName)) {
                    $annotations = $this->reader->getMethodAnnotations($method);
                    foreach ($annotations as $annotation) {
                        $annotationName = explode('\\', get_class($annotation));
                        $commands[$name][array_pop($annotationName)][] = $annotation;
                    }
                }
            }
        }

        return array($commands, $resources);
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

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass($file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = '';
                do {
                    $namespace .= $token[1];
                    $token = $tokens[++$i];
                } while ($i < $count && is_array($token) && in_array($token[0], array(T_NS_SEPARATOR, T_STRING)));
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
