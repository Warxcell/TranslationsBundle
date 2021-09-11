<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration;

use Arxy\TranslationsBundle\ArxyTranslationsBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyBaseKernel;

class Kernel extends SymfonyBaseKernel
{
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new ArxyTranslationsBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config.yml');
    }
}
