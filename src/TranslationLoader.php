<?php

namespace ObjectBG\TranslationBundle;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationLoader implements LoaderInterface {

	private $translationRepository;
	private $languageRepository;

	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager) {
		$this->translationRepository = $entityManager->getRepository("ObjectBGTranslationBundle:Translation");
		$this->languageRepository = $entityManager->getRepository("ObjectBGTranslationBundle:Language");
	}

	public function load($resource, $locale, $domain = 'messages') {
		//Load on the db for the specified local
		$language = $this->languageRepository->findOneByLocale($locale);
//		var_dump($domain);
//		die;
		$translations = $this->translationRepository->getTranslations($language, $domain);
		$catalogue = new MessageCatalogue($locale);
		foreach ($translations as $translation) {
			$catalogue->set($translation->getTranslationToken()->getToken(), $translation->getTranslation(), $domain);
		}
		return $catalogue;
	}

}
