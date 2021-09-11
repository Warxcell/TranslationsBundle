<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration\Entity;

use Arxy\TranslationsBundle\Model\Translation;

class TranslationModel implements Translation
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
