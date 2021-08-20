<?php
declare(strict_types=1);

namespace Arxy\TranslationsBundle;

use Exception;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as OriginalTranslator;
use Symfony\Component\Translation\MessageCatalogue;

class Translator extends OriginalTranslator
{
    private Repository $repository;

    /**
     * @required
     */
    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @param string $locale
     */
    protected function loadCatalogue($locale)
    {
        parent::loadCatalogue($locale);

        $catalogue = new MessageCatalogue($locale);

        // Prevents SQLSTATE[HY000] [1049] Unknown database when clearing cache, because Symfony caches the translations
        try {
            $translations = $this->repository->findByLocale($locale);
        } catch (Exception $exception) {
            return;
        }

        foreach ($translations as $translation) {
            $catalogue->set(
                $translation->getToken(),
                $translation->getTranslation(),
                $translation->getCatalogue()
            );
        }

        $this->catalogues[$locale]->addCatalogue($catalogue);

        $this->loadFallbackCatalogues($locale);
    }

    private function loadFallbackCatalogues($locale)
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
