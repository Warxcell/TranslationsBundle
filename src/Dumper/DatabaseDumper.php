<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Dumper;

use Arxy\TranslationsBundle\Repository;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @internal
 */
final class DatabaseDumper implements DumperInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function dump(MessageCatalogue $messages, $options = []): void
    {
        $this->repository->persistCatalogue($messages);
    }
}
