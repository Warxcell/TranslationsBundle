<?php

namespace ObjectBG\TranslationBundle\Dumper;

use Symfony\Component\Translation\Dumper\DumperInterface;
use \Symfony\Component\Translation\MessageCatalogue;

class DatabaseDumper implements DumperInterface {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }

    public function dump(MessageCatalogue $messages, $options = array()) {
        $domains = $messages->all();

        $allTokens = $this->em
                ->createQuery('SELECT token FROM ObjectBGTranslationBundle:TranslationToken token INDEX BY token.token')
                ->getResult();

        foreach ($domains as $domain) {
            foreach ($domain as $token => $val) {
                if (!isset($allTokens[$token])) {
                    $TokenEntity = new \ObjectBG\TranslationBundle\Entity\TranslationToken();
                    $TokenEntity->setToken($token);
                    $this->em->persist($TokenEntity);
                }
            }
        }
        $this->em->flush();
    }

}
