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
    private Repository|null $repository = null;

    private CacheFlag|null $cacheFlag = null;

    private int $version = 0;
    private bool $warmUp = false;

    private ?array $translations = null;

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

        $this->fetchAndMergeTranslations($this->catalogues[$locale]);
    }

    private function fetchAndMergeTranslations(MessageCatalogueInterface $catalogue): void
    {
        if ($this->translations === null) {
            if ($this->warmUp) {
                // do not load translations from database during warmup.
                return;
            }

            $this->translations = [];
            foreach ($this->repository?->fetchTranslations() ?? [] as $translation) {
                $this->translations[$translation->getLocale()][$translation->getCatalogue()][$translation->getToken(
                )] = $translation->getTranslation();
            }

            $this->version = $this->cacheFlag?->getVersion()?->get() ?? 0;
        }

        do {
            $translations = $this->translations[$catalogue->getLocale()] ?? [];

            foreach ($translations as $domain => $messages) {
                foreach ($messages as $token => $translation) {
                    $catalogue->set($token, $translation, $domain);
                }
            }

            $catalogue = $catalogue->getFallbackCatalogue();
        } while ($catalogue !== null);
    }

    public function reset(): void
    {
        $version = $this->cacheFlag?->getVersion();
        if ($version?->get() === $this->version) {
            return;
        }

        $this->catalogues = [];
        $this->translations = null;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->warmUp = $buildDir !== null;
        try {
            return parent::warmUp($cacheDir);
        } finally {
            $this->warmUp = false;
        }
    }
}
