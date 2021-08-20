<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Dumper;

use Arxy\TranslationsBundle\Entity\TranslationToken;
use Arxy\TranslationsBundle\Entity\TranslationTokenRepository;
use Arxy\TranslationsBundle\Repository;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseDumper implements DumperInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function dump(MessageCatalogue $messages, $options = [])
    {
        $this->repository->persistCatalogue($messages);
    }
}
