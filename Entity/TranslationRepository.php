<?php
declare(strict_types=1);

namespace Arxy\TranslationsBundle\Entity;

use Arxy\EntityTranslationsBundle\Model\Language;
use Doctrine\ORM\EntityRepository;

class TranslationRepository extends EntityRepository
{
    public function getTranslations(Language $language, $catalogue = "messages")
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT t,token FROM ".Translation::class." t JOIN t.translationToken token WHERE t.language = :language AND token.catalogue = :catalogue"
        );
        $query->setParameter("language", $language);
        $query->setParameter("catalogue", $catalogue);
        $r = $query->getResult();

        return $r;
    }

    public function getTranslationsByLocale($locale, $catalogue = "messages")
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT t,token,language FROM ".Translation::class." t JOIN t.translationToken token JOIN t.language language WHERE language.locale = :locale AND token.catalogue = :catalogue"
        );
        $query->setParameter("locale", $locale);
        $query->setParameter("catalogue", $catalogue);
        $r = $query->getResult();

        return $r;
    }

    /**
     * @param $locale
     * @return array
     */
    public function getAllTranslationsByLocale($locale)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT translation,token,language FROM ".Translation::class." translation JOIN translation.translationToken token JOIN translation.language language WHERE language.locale = :locale"
        );
        $query->setParameter("locale", $locale);
        $r = $query->getResult();

        return $r;
    }


    public function getTranslationByTokenAndLanguage(TranslationToken $translationToken, Language $language)
    {

        $em = $this->getEntityManager();
        $dql = "SELECT t FROM ".Translation::class." t  WHERE t.translationToken = :token AND t.language = :language";

        $exists = $em->createQuery($dql)
            ->setParameter('token', $translationToken)
            ->setParameter('language', $language)
            ->getOneOrNullResult();

        return $exists;
    }
}
