# TranslationsBundle

Import your translations into database.

## Installation:

###### it is recommented to install X.Y.* version - This project follow <a target="_blank" href="https://semver.org/">semver</a> - Patch versions will be always compatible with each other. Minor versions may contain minor BC-breaks.

- composer require arxy/translations-bundle
- Register bundle in AppKernel.php: `new \Arxy\TranslationsBundle\ArxyTranslationsBundle()`

You need to create the source of your translations by creating Repository which will loads the translations:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="languages")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Language implements \Arxy\TranslationsBundle\Model\Language
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="locale", type="string", length=35, nullable=false)
     * @Assert\NotNull()
     * @Assert\Locale()
     */
    protected ?string $locale = null;

    /**
     * @ORM\Column(name="order_index", type="smallint")
     */
    protected int $orderIndex = 0;

    protected ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getOrderIndex(): int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $orderIndex): void
    {
        $this->orderIndex = $orderIndex;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Entity\Translation;

use App\Entity\Language;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="translation_tokens")
 * @UniqueEntity(fields={"token", "catalogue"}, message="This token already exists")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Token
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /** @ORM\Column(type="text", nullable=false) */
    private ?string $token = null;

    /** @ORM\Column(type="string", length=200, nullable=false) */
    private ?string $catalogue = null;

    /**
     * @var Collection<Translation>
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="token", cascade={"PERSIST", "REMOVE"}, orphanRemoval=true)
     */
    protected $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    
    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getCatalogue(): ?string
    {
        return $this->catalogue;
    }

    public function setCatalogue(?string $catalogue): void
    {
        $this->catalogue = $catalogue;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation(Translation $translation)
    {
        $this->translations->add($translation);
        $translation->setToken($this);
    }

    public function removeTranslation(Translation $translation)
    {
        $this->translations->removeElement($translation);
        $translation->setToken(null);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Entity\Translation;

use App\Entity\Language;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="translations",
 *   uniqueConstraints={@Doctrine\ORM\Mapping\UniqueConstraint(columns={"language_id", "token_id"})}
 * )
 * @UniqueEntity(fields={"language", "token"}, message="This translation already exists")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Translation
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity=Language::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected ?Language $language = null;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity=Token::class, fetch="EAGER", inversedBy="translations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected ?Token $token = null;

    /**
     * @ORM\Column(type="text")
     */
    protected ?string $translation = null;

    public function getTranslation()
    {
        return $this->translation;
    }

    public function setTranslation($translation): void
    {
        $this->translation = $translation;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): void
    {
        $this->language = $language;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setToken(?Token $token): void
    {
        $this->token = $token;
    }
}
```

DTO for Simplified and lightweight loading of translations:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Translation;

class SimpleTranslation implements \Arxy\TranslationsBundle\Model\Translation
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
```

and the Repository:

```php
<?php

declare(strict_types=1);

namespace App\Repository\Translation;

use App\Entity\Language;
use App\Entity\Translation\SimpleTranslation;
use App\Entity\Translation\Token;
use App\Entity\Translation\Translation;
use Arxy\TranslationsBundle\Repository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Translation\MessageCatalogueInterface;

class TranslationRepository extends ServiceEntityRepository implements Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    public function findByLocale(string $locale): iterable
    {
        $qb = $this->createQueryBuilder('translation');
        $qb->select('NEW '.SimpleTranslation::class.'(translation.translation, token.token, token.catalogue)');
        $qb->join('translation.token', 'token');
        $qb->join('translation.language', 'language');
        $qb->andWhere('language.locale = :locale')->setParameter('locale', $locale);
        
        $query = $qb->getQuery();

        //$query->enableResultCache(0, 'translations_'.$locale);

        return $query->toIterable();
    }

    private function exists($token, $catalogue): bool
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('1')
            ->from(Token::class, 'token');
        $qb->andWhere('token.catalogue = :catalogue')
            ->setParameter('catalogue', $catalogue);
        $qb->andWhere('token.token = :token')
            ->setParameter('token', $token);

        try {
            $qb->getQuery()->getSingleScalarResult();

            return true;
        } catch (NoResultException $exception) {
            return false;
        }
    }

    public function persistCatalogue(MessageCatalogueInterface $catalogue): void
    {
        $domains = $catalogue->all();

        $language = $this->getEntityManager()->getRepository(Language::class)->findOneBy(
            [
                'locale' => $catalogue->getLocale(),
            ]
        );

        foreach ($domains as $catalogue => $messages) {
            foreach ($messages as $token => $val) {
                if ($this->exists($token, $catalogue)) {
                    continue;
                }
                $translationToken = new Token();
                $translationToken->setToken($token);
                $translationToken->setCatalogue($catalogue);
                $this->getEntityManager()->persist($translationToken);

                if ($language !== null) {
                    $trans = new Translation();
                    $trans->setLanguage($language);
                    $trans->setToken($translationToken);
                    $trans->setTranslation($val);
                    $this->getEntityManager()->persist($trans);
                }
            }
        }

        $this->getEntityManager()->flush();
    }
}
```

For Object (Entity) Translations see: <a href="https://github.com/Warxcell/EntityTranslationsBundle" target="_blank">
EntityTranslationsBundle</a>

## For initial import of all tokens:

###### ($locale does not matter since tokens are same for all locales and they are stored in single table, but it's required by Symfony)

php app/console translation:update --output-format="db" $locale $bundle --force
