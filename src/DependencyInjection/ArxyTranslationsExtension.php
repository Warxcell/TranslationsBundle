<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\DependencyInjection;

use Arxy\TranslationsBundle\CacheFlag;
use Arxy\TranslationsBundle\Repository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ArxyTranslationsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $container->setAlias(Repository::class, $config['repository']);
        $container->getDefinition(CacheFlag::class)->setArgument('$cache', new Reference($config['cache_flag']));
    }
}
