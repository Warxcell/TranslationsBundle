<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration;

use Arxy\TranslationsBundle\CacheFlag;
use Arxy\TranslationsBundle\Tests\Integration\Entity\Language;
use Arxy\TranslationsBundle\Tests\Integration\Entity\Token;
use Arxy\TranslationsBundle\Tests\Integration\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function sprintf;

class TranslatorTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    private function buildDb(KernelInterface $kernel): void
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $application->run(
            new ArrayInput(
                [
                    'doctrine:schema:create',
                ]
            ),
            new NullOutput()
        );
    }

    public function translateDataProvider(): iterable
    {
        yield 'Translation' => ['Здравей, свят!', 'hello_world', [], 'messages', 'bg'];
        yield 'Non DB Translation' => ['Това идва от messages.bg.yml', 'non_db_translation', [], 'messages', 'bg'];
        yield 'Fallback' => ['How is it?', 'how_is_it', [], 'messages', 'bg'];
        yield 'Second fallback' => ['This message is in NL', 'second_fallback', [], 'messages', 'bg'];
        yield 'Placeholder' => ['Hello, Gosho', 'hello_user', ['%name%' => 'Gosho'], 'messages', 'en'];
        yield 'Placeholder, non-default catalogue' => [
            'User Gosho is wrong!',
            'wrong_user',
            ['%user%' => 'Gosho'],
            'validators',
            'en',
        ];
        yield 'Count, 0' => [
            'Gosho has no apples!',
            'has_apples',
            ['%name%' => 'Gosho', '%count%' => 0],
            'messages',
            'en',
        ];
        yield 'Count, 1' => [
            'Gosho has one apple!',
            'has_apples',
            ['%name%' => 'Gosho', '%count%' => 1],
            'messages',
            'en',
        ];
        yield 'Count, 2' => [
            'Gosho has 2 apples!',
            'has_apples',
            ['%name%' => 'Gosho', '%count%' => 2],
            'messages',
            'en',
        ];
    }

    /**
     * @dataProvider translateDataProvider
     */
    public function testTranslate(
        string $expected,
        string $token,
        array $parameters,
        string $catalogue,
        string $locale
    ): void {
        $kernel = self::bootKernel();
        $this->buildDb($kernel);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $bg = new Language('bg');
        $entityManager->persist($bg);

        $en = new Language('en');
        $entityManager->persist($en);

        $nl = new Language('nl');
        $entityManager->persist($nl);

        $helloWorld = new Token('hello_world', 'messages');
        $entityManager->persist($helloWorld);
        $entityManager->persist(new Translation($bg, $helloWorld, 'Здравей, свят!'));

        $secondFallback = new Token('second_fallback', 'messages');
        $entityManager->persist($secondFallback);
        $entityManager->persist(
            new Translation($nl, $secondFallback, 'This message is in NL')
        );

        $howIsIt = new Token('how_is_it', 'messages');
        $entityManager->persist($howIsIt);
        $entityManager->persist(new Translation($en, $howIsIt, 'How is it?'));

        $helloUser = new Token('hello_user', 'messages');
        $entityManager->persist($helloUser);
        $entityManager->persist(new Translation($en, $helloUser, 'Hello, %name%'));

        $wrongUser = new Token('wrong_user', 'validators');
        $entityManager->persist($wrongUser);
        $entityManager->persist(new Translation($en, $wrongUser, 'User %user% is wrong!'));


        $hasApples = new Token('has_apples', 'messages');
        $entityManager->persist($hasApples);
        $entityManager->persist(
            new Translation(
                $en,
                $hasApples,
                '{0}%name% has no apples!|{1}%name% has one apple!|]1,Inf[ %name% has %count% apples!'
            )
        );
        $entityManager->flush();

        /** @var TranslatorInterface $translator */
        $translator = static::getContainer()->get(TranslatorInterface::class);

        self::assertSame($expected, $translator->trans($token, $parameters, $catalogue, $locale));
    }

    public function testCacheFlag(): void
    {
        $kernel = self::bootKernel();
        $this->buildDb($kernel);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $bg = new Language('bg');
        $entityManager->persist($bg);

        $en = new Language('en');
        $entityManager->persist($en);

        $token = new Token('hello_world', 'messages');
        $entityManager->persist($token);
        $helloWorldInBg = new Translation($bg, $token, 'Здравей, свят!');
        $helloWorldInEn = new Translation($en, $token, 'Hello, world!');
        $entityManager->persist($helloWorldInBg);
        $entityManager->persist($helloWorldInEn);
        $entityManager->flush();

        /** @var TranslatorInterface $translator */
        $translator = static::getContainer()->get(TranslatorInterface::class);

        self::assertSame('Здравей, свят!', $translator->trans('hello_world', locale: 'bg'));
        self::assertSame('Hello, world!', $translator->trans('hello_world', locale: 'en'));

        $helloWorldInBg->setTranslation('Здравей, свят! редактирано');
        $entityManager->persist($helloWorldInBg);
        $entityManager->flush();

        $helloWorldInEn->setTranslation('Hello, world! Edited');
        $entityManager->persist($helloWorldInEn);
        $entityManager->flush();

        if (!$translator instanceof ResetInterface) {
            throw new \LogicException();
        }
        $translator->reset();


        self::assertSame('Здравей, свят!', $translator->trans('hello_world', locale: 'bg'));
        self::assertSame('Hello, world!', $translator->trans('hello_world', locale: 'en'));

        /** @var CacheFlag $cacheFlag */
        $cacheFlag = static::getContainer()->get(CacheFlag::class);
        $cacheFlag->increment($cacheFlag->getVersion());

        $translator->reset();

        self::assertSame('Здравей, свят! редактирано', $translator->trans('hello_world', locale: 'bg'));
        self::assertSame('Hello, world! Edited', $translator->trans('hello_world', locale: 'en'));
    }

    public function testDeletedTranslation(): void
    {
        $kernel = self::bootKernel();
        $this->buildDb($kernel);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $bg = new Language('bg');
        $entityManager->persist($bg);

        $en = new Language('en');
        $entityManager->persist($en);

        $token = new Token('hello_world', 'messages');
        $entityManager->persist($token);
        $helloWorldInBg = new Translation($bg, $token, 'Здравей, свят!');
        $helloWorldInEn = new Translation($en, $token, 'Hello, world!');
        $entityManager->persist($helloWorldInBg);
        $entityManager->persist($helloWorldInEn);
        $entityManager->flush();

        /** @var TranslatorInterface $translator */
        $translator = static::getContainer()->get(TranslatorInterface::class);

        self::assertSame('Здравей, свят!', $translator->trans('hello_world', locale: 'bg'));

        $entityManager->remove($helloWorldInBg);
        $entityManager->flush();

        /** @var CacheFlag $cacheFlag */
        $cacheFlag = static::getContainer()->get(CacheFlag::class);
        $cacheFlag->increment($cacheFlag->getVersion());

        if (!$translator instanceof ResetInterface) {
            throw new \LogicException(
                sprintf('"%s" should not implement "%s"', $translator::class, ResetInterface::class)
            );
        }
        $translator->reset();

        // deleted BG trans should fallback to EN
        self::assertSame('Hello, world!', $translator->trans('hello_world', locale: 'bg'));
    }

    public function testCatalogues(): void
    {
        $kernel = self::bootKernel();
        $this->buildDb($kernel);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $en = new Language('en');
        $entityManager->persist($en);

        $enGB = new Language('en-gb');
        $entityManager->persist($enGB);

        $token = new Token('hello_world', 'messages');
        $entityManager->persist($token);
        $helloWorldInEn = new Translation($en, $token, 'Hello, world!');
        $entityManager->persist($helloWorldInEn);
        $entityManager->flush();


        $translator = static::getContainer()->get(TranslatorInterface::class);

        if (!$translator instanceof TranslatorBagInterface) {
            throw new \LogicException();
        }

        $catalogue = $translator->getCatalogue('en-gb');

        $translations = [];

        do {
            $translations += $catalogue->all('messages');
        } while ($catalogue = $catalogue->getFallbackCatalogue());

        self::assertEquals('Hello, world!', $translations['hello_world']);


        $helloWorldInEn->setTranslation('Hello, world! EDITED');
        $entityManager->persist($helloWorldInEn);
        $entityManager->flush();


        /** @var CacheFlag $cacheFlag */
        $cacheFlag = static::getContainer()->get(CacheFlag::class);
        $cacheFlag->increment($cacheFlag->getVersion());

        if (!$translator instanceof ResetInterface) {
            throw new \LogicException();
        }
        $translator->reset();

        $catalogue = $translator->getCatalogue('en-gb');

        $translations = [];

        do {
            $translations += $catalogue->all('messages');
        } while ($catalogue = $catalogue->getFallbackCatalogue());

        self::assertEquals('Hello, world! EDITED', $translations['hello_world']);
    }

    /**
     * When Symfony clear cache, tables might not be created, no exception should be thrown in that case.
     */
    public function testCacheClear(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setCatchExceptions(false);
        $application->setAutoExit(false);

        $application->run(
            new ArrayInput(
                [
                    'cache:clear',
                ]
            ),
            new NullOutput()
        );
        $this->expectNotToPerformAssertions();
    }
}
