<?php

namespace ObjectBG\TranslationBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ObjectBGTranslationBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DependencyInjection\Compiler\TemplatingCompilerPass);
        $container->addCompilerPass(new DependencyInjection\Compiler\OverrideTranslatorCompilerPass());
    }
}
