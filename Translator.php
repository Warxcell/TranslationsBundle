<?php

namespace ObjectBG\TranslationBundle;

use Doctrine\Common\Persistence\ManagerRegistry;
use ObjectBG\TranslationBundle\Entity\Translation;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as OriginalTranslator;
use Symfony\Component\Translation\MessageCatalogue;

class Translator extends OriginalTranslator
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param string $locale
     */
    protected function loadCatalogue($locale)
    {
        parent::loadCatalogue($locale);

        /* @var $translationRepository \ObjectBG\TranslationBundle\Repository\Translation */
        $translationRepository = $this->doctrine->getRepository(Translation::class);

        $catalogue = new MessageCatalogue($locale);

        $translations = $translationRepository->getAllTranslationsByLocale($locale);
        foreach ($translations as $translation) {
            $catalogue->set(
                $translation->getTranslationToken()->getToken(),
                $translation->getTranslation(),
                $translation->getTranslationToken()->getCatalogue()
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
