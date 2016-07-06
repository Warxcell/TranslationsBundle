<?php

namespace ObjectBG\TranslationBundle;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationLoader implements LoaderInterface
{

    private $translationRepository;
    private $languageRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->translationRepository = $entityManager->getRepository("ObjectBGTranslationBundle:Translation");
        $this->languageRepository = $entityManager->getRepository("ObjectBGTranslationBundle:Language");
    }

    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = new MessageCatalogue($locale);
        $language = $this->languageRepository->findOneByLocale($locale);

        if ($language) {
            $translations = $this->translationRepository->getTranslations($language, $domain);
            foreach ($translations as $translation) {
                $catalogue->set($translation->getTranslationToken()->getToken(), $translation->getTranslation(), $domain);
            }
        }

        return $catalogue;
    }

}
