<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'languages')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Language
{
    #[ORM\Id]
    #[ORM\Column(name: 'locale', type: 'string', length: 35, nullable: false)]
    #[Assert\NotNull]
    #[Assert\Locale]
    protected ?string $locale = null;

    public function __construct(?string $locale)
    {
        $this->locale = $locale;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
