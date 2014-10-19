<?php

namespace ObjectBG\TranslationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ObjectBG\TranslationBundle\Entity\Language;
use ObjectBG\TranslationBundle\Entity\Translation;

class Translation extends EntityRepository {

    /**
     * Return all translations for specified token
     * @param Language $language
     * @param type $catalogue
     * @return <Translation>
     */
    public function getTranslations(Language $language, $catalogue = "messages") {
        $query = $this->getEntityManager()->createQuery("SELECT t,token FROM ObjectBGTranslationBundle:Translation t JOIN t.translationToken token WHERE t.language = :language AND t.catalogue = :catalogue");
        $query->setParameter("language", $language);
        $query->setParameter("catalogue", $catalogue);
        $r = $query->getResult();
        return $r;
    }

}
