<?php

namespace ObjectBG\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder;

class TemplatingCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $resources = $container->getParameter('twig.form.resources');

        $template = 'ObjectBGTranslationBundle:Form:default.html.twig';
        if (!in_array($template, $resources)) {
            $resources[] = $template;
            $container->setParameter('twig.form.resources', $resources);
        }
    }
}
