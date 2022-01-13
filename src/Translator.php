<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as OriginalTranslator;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @internal
 */
class Translator extends OriginalTranslator
{
    private Repository $repository;
    private bool $warmUp = false;

    /**
     * @required
     */
    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
    }

    protected function loadCatalogue(string $locale): void
    {
        parent::loadCatalogue($locale);

        if ($this->warmUp) {
            // do not load translations from database during warmup.
            return;
        }

        $translations = $this->repository->findByLocale($locale);
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

    public function warmUp(string $cacheDir)
    {
        $this->warmUp = true;
        try {
            return parent::warmUp($cacheDir);
        } finally {
            $this->warmUp = false;
        }
    }
}
