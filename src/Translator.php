<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle;

use Doctrine\DBAL\Exception\DatabaseObjectNotFoundException;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as OriginalTranslator;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @internal
 */
class Translator extends OriginalTranslator
{
    private Repository $repository;

    /**
     * @phpcsSuppress PEAR.Functions.ValidDefaultValue
     */
    public function __construct(
        ContainerInterface $container,
        MessageFormatterInterface $formatter,
        string $defaultLocale,
        array $loaderIds = [],
        array $options = [],
        array $enabledLocales = [],
        Repository $repository
    ) {
        parent::__construct($container, $formatter, $defaultLocale, $loaderIds, $options, $enabledLocales);
        $this->repository = $repository;
    }

    protected function loadCatalogue(string $locale): void
    {
        parent::loadCatalogue($locale);

        try {
            $translations = $this->repository->findByLocale($locale);
        } catch (DatabaseObjectNotFoundException $exception) {
            // Prevents SQLSTATE[HY000] [1049] Unknown database when clearing cache, because Symfony caches the translations
            return;
        }
        $catalogue = $this->catalogues[$locale];
        foreach ($translations as $translation) {
            $catalogue->set(
                $translation->getToken(),
                $translation->getTranslation(),
                $translation->getCatalogue()
            );
        }
        $this->loadFallbackTranslations($catalogue);
    }

    private function loadFallbackTranslations(MessageCatalogueInterface $catalogue): void
    {
        while (($catalogue = $catalogue->getFallbackCatalogue()) !== null) {
            $translations = $this->repository->findByLocale($catalogue->getLocale());

            foreach ($translations as $translation) {
                $catalogue->set(
                    $translation->getToken(),
                    $translation->getTranslation(),
                    $translation->getCatalogue()
                );
            }
        }
    }
}
