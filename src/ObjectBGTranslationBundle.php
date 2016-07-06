<?php

namespace ObjectBG\TranslationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
