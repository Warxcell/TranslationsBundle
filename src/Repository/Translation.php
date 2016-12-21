<?php

namespace ObjectBG\TranslationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ObjectBG\TranslationBundle\Entity\Language as LanguageEntity;

class Translation extends EntityRepository
{

    /**
     * Return all translations for specified token
     * @param LanguageEntity $language
     * @param type $catalogue
     * @return <Translation>
     */
    public function getTranslations(LanguageEntity $language, $catalogue = "messages")
    {
        $query = $this->getEntityManager()->createQuery("SELECT t,token FROM ObjectBGTranslationBundle:Translation t JOIN t.translationToken token WHERE t.language = :language AND t.catalogue = :catalogue");
        $query->setParameter("language", $language);
        $query->setParameter("catalogue", $catalogue);
        $r = $query->getResult();
        return $r;
    }
}
