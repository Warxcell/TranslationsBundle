<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'translations', uniqueConstraints: [new ORM\UniqueConstraint(columns: ['language_id', 'token_id'])])]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Translation
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Language::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(referencedColumnName: 'locale', nullable: false, onDelete: 'CASCADE')]
    protected Language $language;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Token::class, cascade: ['ALL'], fetch: 'EAGER', inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Token $token;

    #[ORM\Column(type: 'text')]
    protected string $translation;

    public function __construct(Language $language, Token $token, $translation)
    {
        $this->language = $language;
        $this->token = $token;
        $this->translation = $translation;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setTranslation(string $translation): void
    {
        $this->translation = $translation;
    }

    public function getTranslation(): string
    {
        return $this->translation;
    }
}
