<?php
declare(strict_types=1);

namespace Arxy\TranslationBundle\Model;

interface Language
{
    public function getLocale(): string;
}