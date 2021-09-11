<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle;

use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as OriginalTranslator;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Component\Translation\MessageCatalogue;

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

        // Prevents SQLSTATE[HY000] [1049] Unknown database when clearing cache, because Symfony caches the translations
        try {
            $translations = $this->repository->findByLocale($locale);
        } catch (Exception $exception) {
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

        $this->loadFallbackCatalogues($locale);
    }

    private function loadFallbackCatalogues(string $locale): void
    {
        $current = $this->catalogues[$locale];

        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            if (!isset($this->catalogues[$fallback])) {
                $this->loadCatalogue($fallback);
            }

            $fallbackCatalogue = new MessageCatalogue($fallback, $this->catalogues[$fallback]->all());
            foreach ($this->catalogues[$fallback]->getResources() as $resource) {
                $fallbackCatalogue->addResource($resource);
            }
            $current->addFallbackCatalogue($fallbackCatalogue);
            $current = $fallbackCatalogue;
        }
    }
}
