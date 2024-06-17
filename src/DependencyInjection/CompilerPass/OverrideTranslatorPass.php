<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\DependencyInjection\CompilerPass;

use Arxy\TranslationsBundle\CacheFlag;
use Arxy\TranslationsBundle\Repository;
use Arxy\TranslationsBundle\Translator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final readonly class OverrideTranslatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('translator.default')
            ->setClass(Translator::class)
            ->addTag('kernel.reset', ['method' => 'reset'])
            ->addMethodCall('setRepository', [new Reference(Repository::class)])
            ->addMethodCall('setCacheFlag', [new Reference(CacheFlag::class)]);
    }
}
