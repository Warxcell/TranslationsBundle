<?php

namespace ObjectBG\TranslationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class Translation extends EntityRepository {

	/**
	 * Return all translations for specified token
	 * @param type $language
	 * @param type $catalogue 
	 */
	public function getTranslations($language, $catalogue = "messages") {
		$query = $this->getEntityManager()->createQuery("SELECT t,token FROM ObjectBGTranslationBundle:Translation t JOIN t.translationToken token WHERE t.language = :language AND t.catalogue = :catalogue");
		$query->setParameter("language", $language);
		$query->setParameter("catalogue", $catalogue);
		$r = $query->getResult();
		return $r;
	}

}
