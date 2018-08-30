<?php

namespace ObjectBG\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideTranslatorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('translator.default')
            ->setClass('ObjectBG\TranslationBundle\Translator')
            ->addMethodCall('setDoctrine', [new Reference('doctrine')]);
    }
}
