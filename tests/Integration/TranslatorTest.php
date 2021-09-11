<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Tests\Integration;

use Arxy\TranslationsBundle\Tests\Integration\Entity\Language;
use Arxy\TranslationsBundle\Tests\Integration\Entity\Token;
use Arxy\TranslationsBundle\Tests\Integration\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
            new ConsoleOutput()
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

        $entityManager->persist(new Translation($bg, new Token('hello_world', 'messages'), 'Здравей, свят!'));
        $entityManager->persist(
            new Translation($nl, new Token('second_fallback', 'messages'), 'This message is in NL')
        );
        $entityManager->persist(new Translation($en, new Token('how_is_it', 'messages'), 'How is it?'));
        $entityManager->persist(new Translation($en, new Token('hello_user', 'messages'), 'Hello, %name%'));
        $entityManager->persist(new Translation($en, new Token('wrong_user', 'validators'), 'User %user% is wrong!'));
        $entityManager->persist(
            new Translation(
                $en,
                new Token('has_apples', 'messages'),
                '{0}%name% has no apples!|{1}%name% has one apple!|]1,Inf[ %name% has %count% apples!'
            )
        );
        $entityManager->flush();

        /** @var TranslatorInterface $translator */
        $translator = static::getContainer()->get(TranslatorInterface::class);

        self::assertSame($expected, $translator->trans($token, $parameters, $catalogue, $locale));
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
            new ConsoleOutput()
        );
        $this->expectNotToPerformAssertions();
    }
}
