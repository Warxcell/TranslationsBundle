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
        /* @var $languageRepository \ObjectBG\TranslationBundle\Repository\Language  */
        $languageRepository = $em->getRepository("ObjectBGTranslationBundle:Language");

        $language = $languageRepository->findOneByLocale($locale);


        $domain = 'messages';

        $catalogue = new MessageCatalogue($locale);
        if ($language) {
            $translations = $translationRepository->getTranslations($language, $domain);
            foreach ($translations as $translation) {
                $catalogue->set($translation->getTranslationToken()->getToken(), $translation->getTranslation(), $domain);
            }
        }

        $this->catalogues[$locale]->addCatalogue($catalogue);
    }

}
