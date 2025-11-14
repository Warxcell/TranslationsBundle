<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Model;

/**
 * Lightweight DTO to avoid heavy hydrating of ORM
 */
final readonly class TranslationModel implements Translation
{
    public function __construct(
        private string $translation,
        private string $token,
        private string $catalogue,
        private string $locale,
    ) {
    }

    public function getTranslation(): string
    {
        return $this->translation;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getCatalogue(): string
    {
        return $this->catalogue;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
