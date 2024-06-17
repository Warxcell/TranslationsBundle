<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Dumper;

use Arxy\TranslationsBundle\Repository;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @internal
 */
final readonly class DatabaseDumper implements DumperInterface
{
    public function __construct(
        private Repository $repository
    ) {
    }

    public function dump(MessageCatalogue $messages, $options = []): void
    {
        $this->repository->persistCatalogue($messages);
    }
}
