<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as OriginalTranslator;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
class Translator extends OriginalTranslator implements ResetInterface
{
    /**
     * @var MessageCatalogueInterface[]
     */
    private array $originalCatalogues = [];

    private Repository|null $repository = null;

    private CacheFlag|null $cacheFlag = null;

    private int $version = 0;
    private bool $warmUp = false;

    /**
     * @required
     */
    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @required
     */
    public function setCacheFlag(CacheFlag $cacheFlag): void
    {
        $this->cacheFlag = $cacheFlag;
    }

    protected function loadCatalogue(string $locale): void
    {
        parent::loadCatalogue($locale);

        if ($this->warmUp) {
            // do not load translations from database during warmup.
            return;
        }

        $this->originalCatalogues[$locale] = clone $this->catalogues[$locale];
        $this->fetchTranslations($locale);
    }

    private function fetchTranslations(string $locale): void
    {
        $translations = $this->repository?->findByLocale($locale) ?? [];
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

    public function reset(): void
    {
        $version = $this->cacheFlag?->getVersion();
        if ($version?->get() === $this->version) {
            return;
        }

        foreach (array_keys($this->catalogues) as $locale) {
            $catalogue = $this->catalogues[$locale] = clone $this->originalCatalogues[$locale];

            $translations = $this->repository->findByLocale($locale);
            foreach ($translations as $translation) {
                $catalogue->set(
                    $translation->getToken(),
                    $translation->getTranslation(),
                    $translation->getCatalogue()
                );
            }
        }
        $this->version = $version->get();
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->warmUp = true;
        try {
            return parent::warmUp($cacheDir);
        } finally {
            $this->warmUp = false;
        }
    }
}
