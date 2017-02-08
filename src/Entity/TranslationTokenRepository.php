<?php

namespace ObjectBG\TranslationBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TranslationTokenRepository extends EntityRepository
{
    public function checkExist($token, $catalogue)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT COUNT(token) FROM ObjectBGTranslationBundle:TranslationToken token WHERE token.token = :token AND token.catalogue = :catalogue';


        $exists = ((int)$em->createQuery($dql)
                ->setParameter('token', $token)
                ->setParameter('catalogue', $catalogue)
                ->getSingleScalarResult()) > 0;

        return $exists;
    }

    public function findByTokenAndCatalogue($token, $catalogue)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT token FROM ObjectBGTranslationBundle:TranslationToken token WHERE token.token = :token AND token.catalogue = :catalogue';


        $exists = $em->createQuery($dql)
            ->setParameter('token', $token)
            ->setParameter('catalogue', $catalogue)
            ->getOneOrNullResult();

        return $exists;
    }

    public function getAllTokensByLocale($locale)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT token, translation FROM ObjectBGTranslationBundle:TranslationToken token JOIN token.translations translation JOIN translation.language language WITH language.locale = :locale';
        $exists = $em->createQuery($dql)
            ->setParameter('locale', $locale)
            ->getResult();

        return $exists;
    }
}
