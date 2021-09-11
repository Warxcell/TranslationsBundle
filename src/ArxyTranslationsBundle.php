<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle;

use Arxy\TranslationsBundle\DependencyInjection\CompilerPass\OverrideTranslatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ArxyTranslationsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideTranslatorPass());
    }
}
