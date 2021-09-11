<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle;

use Arxy\TranslationsBundle\Model\Translation;
use Symfony\Component\Translation\MessageCatalogueInterface;

interface Repository
{
    /** @return Translation[] */
    public function findByLocale(string $locale): iterable;

    public function persistCatalogue(MessageCatalogueInterface $catalogue): void;
}
