<?php

namespace ObjectBG\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="ObjectBG\TranslationBundle\Repository\Translation")
 * @ORM\Table(name="translations",
 *   uniqueConstraints={@Doctrine\ORM\Mapping\UniqueConstraint(columns={"language_id", "translation_token_id", "catalogue"})}
 * )
 * @UniqueEntity(fields={"language", "translationToken", "catalogue"}, message="This translation already exists")
 */
class Translation {

    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** @ORM\column(type="string", length=200) */
    protected $catalogue;

    /** @ORM\column(type="text") */
    protected $translation;

    /**
     * @ORM\ManyToOne(targetEntity="Language", fetch="EAGER")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", nullable=false)
     * @var Language
     */
    protected $language;

    /**
     * @ORM\ManyToOne(targetEntity="TranslationToken", fetch="EAGER", inversedBy="translations")
     * @ORM\JoinColumn(name="translation_token_id", referencedColumnName="id", nullable=false) 
     * @var TranslationToken
     */
    protected $translationToken;

    public function getId() {
        return $this->id;
    }

    public function getCatalogue() {
        return $this->catalogue;
    }

    public function getTranslation() {
        return $this->translation;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getTranslationToken() {
        return $this->translationToken;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCatalogue($catalogue) {
        $this->catalogue = $catalogue;
    }

    public function setTranslation($translation) {
        $this->translation = $translation;
    }

    /**
     * 
     * @param \ObjectBG\TranslationBundle\Entity\Language $language
     */
    public function setLanguage(Language $language) {
        $this->language = $language;
    }

    /**
     * 
     * @param \ObjectBG\TranslationBundle\Entity\TranslationToken $translationToken
     */
    public function setTranslationToken(TranslationToken $translationToken) {
        $this->translationToken = $translationToken;
    }

    public function __toString() {
        return (string) $this->translation;
    }

}
