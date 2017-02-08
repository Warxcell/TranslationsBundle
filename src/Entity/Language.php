<?php

namespace ObjectBG\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="ObjectBG\TranslationBundle\Entity\LanguageRepository")
 * @ORM\Table(name="languages")
 * @UniqueEntity(fields={"locale"}, message="This locale already exists")
 * @UniqueEntity(fields={"name"}, message="This name already exists")
 */
class Language
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @Assert\Locale()
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=200, unique=true)
     */
    private $locale;

    /**
     * @Assert\NotBlank
     * @ORM\column(type="string", length=200, unique=true)
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
