<?php

namespace Orkestra\Bundle\GuzzleBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Processor;

/**
 * Guzzle Extension
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class GuzzleExtension extends Extension
{
    /**
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $processor = new Processor();
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);

        $loader->load('services.xml');

        $services = array();
        foreach ($config['services'] as $key => $service) {
            $args = array();
            $args[] = $service['params'];

            if ($service['services']) {
                foreach ($service['services'] as $value) {
                    $args[] = $container->get($value['id']);
                }
            }

            $service['args'] = $args;
            $service['name'] = $key;
            $services[$key] = $service;
        }

        $container->setParameter('guzzle.config.services', $services);
    }
}