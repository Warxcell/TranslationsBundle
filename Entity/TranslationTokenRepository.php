<?php
declare(strict_types=1);

namespace Arxy\TranslationsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TranslationTokenRepository extends EntityRepository
{
    public function checkIfExists($token, $catalogue)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT COUNT(token) FROM '.TranslationToken::class.' token WHERE token.token = :token AND token.catalogue = :catalogue';


        $exists = ((int)$em->createQuery($dql)
                ->setParameter('token', $token)
                ->setParameter('catalogue', $catalogue)
                ->getSingleScalarResult()) > 0;

        return $exists;
    }

    public function findByTokenAndCatalogue($token, $catalogue)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT token FROM '.TranslationToken::class.' token WHERE token.token = :token AND token.catalogue = :catalogue';


        $exists = $em->createQuery($dql)
            ->setParameter('token', $token)
            ->setParameter('catalogue', $catalogue)
            ->getOneOrNullResult();

        return $exists;
    }

    public function getAllTokensByLocale($locale)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT token, translation FROM '.TranslationToken::class.' token JOIN token.translations translation JOIN translation.language LANGUAGE WITH LANGUAGE.locale = :locale';
        $exists = $em->createQuery($dql)
            ->setParameter('locale', $locale)
            ->getResult();

        return $exists;
    }
}
