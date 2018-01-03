<?php

namespace ObjectBG\TranslationBundle\Dumper;

use Doctrine\ORM\EntityManager;
use ObjectBG\TranslationBundle\Entity\TranslationToken;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseDumper implements DumperInterface
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function dump(MessageCatalogue $messages, $options = array())
    {
        $domains = $messages->all();

        $dql = 'SELECT COUNT(token) FROM ObjectBGTranslationBundle:TranslationToken token WHERE token.token = :token AND token.catalogue = :catalogue';

        foreach ($domains as $catalogue => $messages) {
            foreach ($messages as $token => $val) {
                $exists = ((int)$this->em->createQuery($dql)
                        ->setParameter('token', $token)
                        ->setParameter('catalogue', $catalogue)
                        ->getSingleScalarResult()) > 0;
                if (!$exists) {
                    $translationToken = new TranslationToken();
                    $translationToken->setToken($token);
                    $translationToken->setCatalogue($catalogue);
                    $this->em->persist($translationToken);
                }
            }
        }
        $this->em->flush();
    }
}
