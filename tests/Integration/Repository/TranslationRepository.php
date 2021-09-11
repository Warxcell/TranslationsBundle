<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration\Repository;

use Arxy\TranslationsBundle\Model\TranslationModel;
use Arxy\TranslationsBundle\Repository;
use Arxy\TranslationsBundle\Tests\Integration\Entity\Language;
use Arxy\TranslationsBundle\Tests\Integration\Entity\Token;
use Arxy\TranslationsBundle\Tests\Integration\Entity\Translation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Translation\MessageCatalogueInterface;

class TranslationRepository extends ServiceEntityRepository implements Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    public function findByLocale(string $locale): iterable
    {
        $qb = $this->createQueryBuilder('translation');
        $qb->select('NEW ' . TranslationModel::class . '(translation.translation, token.token, token.catalogue)');
        $qb->join('translation.token', 'token');
        $qb->join('translation.language', 'language');
        $qb->andWhere('language.locale = :locale')->setParameter('locale', $locale);
        $query = $qb->getQuery();

        return $query->toIterable();
    }

    private function exists($token, $catalogue): bool
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('1')
            ->from(Token::class, 'token');
        $qb->andWhere('token.catalogue = :catalogue')
            ->setParameter('catalogue', $catalogue);
        $qb->andWhere('token.token = :token')
            ->setParameter('token', $token);

        try {
            $qb->getQuery()->getSingleScalarResult();

            return true;
        } catch (NoResultException $exception) {
            return false;
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function persistCatalogue(MessageCatalogueInterface $catalogue): void
    {
        $domains = $catalogue->all();

        $language = $this->getEntityManager()->getRepository(Language::class)->findOneBy(
            [
                'locale' => $catalogue->getLocale(),
            ]
        );

        foreach ($domains as $catalogue => $messages) {
            foreach ($messages as $token => $val) {
                if ($this->exists($token, $catalogue)) {
                    continue;
                }
                $translationToken = new Token($token, $catalogue);
                $this->getEntityManager()->persist($translationToken);

                if ($language !== null) {
                    $trans = new Translation($language, $translationToken, $val);
                    $this->getEntityManager()->persist($trans);
                }
            }
        }

        $this->getEntityManager()->flush();
    }
}

