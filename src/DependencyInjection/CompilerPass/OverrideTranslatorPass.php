<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\DependencyInjection\CompilerPass;

use Arxy\TranslationsBundle\Repository;
use Arxy\TranslationsBundle\Translator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideTranslatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('translator.default')
            ->setClass(Translator::class)
            ->setArgument('$repository', new Reference(Repository::class));
    }
}
