<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="translation_tokens")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Token
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue()
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private string $token;

    /**
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private string $catalogue;

    /**
     * @var Collection<Translation>
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="token", cascade={"PERSIST", "REMOVE"}, orphanRemoval=true)
     */
    private Collection $translations;

    public function __construct(string $token, string $catalogue)
    {
        $this->translations = new ArrayCollection();
        $this->token = $token;
        $this->catalogue = $catalogue;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getCatalogue(): string
    {
        return $this->catalogue;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation(Translation $translation): void
    {
        $this->translations->add($translation);
        $translation->setToken($this);
    }

    public function removeTranslation(Translation $translation): void
    {
        $this->translations->removeElement($translation);
        $translation->setToken(null);
    }
}
