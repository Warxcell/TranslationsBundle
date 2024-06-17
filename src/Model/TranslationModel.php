<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Model;

/**
 * Lightweight DTO to avoid heavy hydrating of ORM
 */
final readonly class TranslationModel implements Translation
{
    private string $translation;
    private string $token;
    private string $catalogue;

    public function __construct(string $translation, string $token, string $catalogue)
    {
        $this->translation = $translation;
        $this->token = $token;
        $this->catalogue = $catalogue;
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
}
