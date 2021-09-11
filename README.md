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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="languages")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Language
{
    /**
     * @ORM\Id
     * @ORM\Column(name="locale", type="string", length=35, nullable=false)
     * @Assert\NotNull()
     * @Assert\Locale()
     */
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
```

```php
<?php

declare(strict_types=1);

namespace App\Entity;

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
}
```

```php
<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="translations",
 *   uniqueConstraints={@ORM\UniqueConstraint(columns={"language_id", "token_id"})}
 * )
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Translation
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity=Language::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="locale", onDelete="CASCADE")
     */
    protected Language $language;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity=Token::class, fetch="EAGER", inversedBy="translations", cascade={"ALL"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected Token $token;

    /**
     * @ORM\Column(type="text")
     */
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

    public function getTranslation(): string
    {
        return $this->translation;
    }
}
```

and the Repository:

```php
<?php

declare(strict_types=1);

namespace App\Repository

use Arxy\TranslationsBundle\Model\TranslationModel;
use Arxy\TranslationsBundle\Repository;
use App\Entity\Language;
use App\Entity\Token;
use App\Entity\Translation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
        $qb->select('NEW ' . TranslationModel::class . '(translation.translation, token.token, token.catalogue)');
        $qb->join('translation.token', 'token');
        $qb->join('translation.language', 'language');
        $qb->andWhere('language.locale = :locale')->setParameter('locale', $locale);
        $query = $qb->getQuery();

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

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
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
                $translationToken = new Token($token, $catalogue);
                $this->getEntityManager()->persist($translationToken);

                if ($language !== null) {
                    $trans = new Translation($language, $translationToken, $val);
                    $this->getEntityManager()->persist($trans);
                }
            }
        }

        $this->getEntityManager()->flush();
    }
}

```

For Object (Entity) Translations see:
<a href="https://github.com/Warxcell/EntityTranslationsBundle" target="_blank">EntityTranslationsBundle</a>

## To update database with Tokens:

###### ($locale does not matter since tokens are same for all locales and they are stored in single table, but it's required by Symfony)

`php bin/console translation:update --output-format="db" $locale --force --no-interaction --prefix=`
