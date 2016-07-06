<?php

namespace ObjectBG\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideTranslatorCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $defaultTranslator = $container->getDefinition('translator.default');
        $defaultTranslator->setClass('ObjectBG\TranslationBundle\Translator');
    }

}
