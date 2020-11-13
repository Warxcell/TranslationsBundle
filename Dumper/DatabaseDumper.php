<?php
declare(strict_types=1);

namespace Arxy\TranslationsBundle\Dumper;

use Arxy\TranslationsBundle\Entity\TranslationToken;
use Arxy\TranslationsBundle\Entity\TranslationTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseDumper implements DumperInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function dump(MessageCatalogue $messages, $options = array())
    {
        $domains = $messages->all();

        /** @var TranslationTokenRepository $translationTokenRepository */
        $translationTokenRepository = $this->em->getRepository(TranslationToken::class);

        foreach ($domains as $catalogue => $messages) {
            foreach ($messages as $token => $val) {
                $exists = $translationTokenRepository->checkIfExists($token, $catalogue);
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
