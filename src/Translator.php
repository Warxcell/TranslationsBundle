<?php

namespace ObjectBG\TranslationBundle;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as OriginalTranslator;
use Symfony\Component\Translation\MessageCatalogue;

class Translator extends OriginalTranslator
{

    /**
     * @todo add support for multiple domains
     * @param string $locale
     */
    protected function loadCatalogue($locale)
    {
        parent::loadCatalogue($locale);

        $em = $this->container->get('doctrine.orm.entity_manager');
        /* @var $translationRepository \ObjectBG\TranslationBundle\Repository\Translation  */
        $translationRepository = $em->getRepository("ObjectBGTranslationBundle:Translation");

        $domain = 'messages';
        $catalogue = new MessageCatalogue($locale);

        $translations = $translationRepository->getTranslationsByLocale($locale, $domain);
        foreach ($translations as $translation) {
            $catalogue->set($translation->getTranslationToken()->getToken(), $translation->getTranslation(), $domain);
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
