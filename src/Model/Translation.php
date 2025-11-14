<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Model;

interface Translation
{
    public function getTranslation(): string;

    public function getToken(): string;

    public function getCatalogue(): string;

    public function getLocale(): string;
}
