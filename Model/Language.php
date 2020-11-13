<?php
declare(strict_types=1);

namespace Arxy\TranslationsBundle\Model;

interface Language
{
    public function getLocale(): string;
}