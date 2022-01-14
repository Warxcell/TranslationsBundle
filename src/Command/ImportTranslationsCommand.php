<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Command;

use Arxy\TranslationsBundle\Model\Language;
use Arxy\TranslationsBundle\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorBagInterface;

class ImportTranslationsCommand extends Command
{
    protected static $defaultName = 'arxy:translations:import-translations';
    protected static $defaultDescription = 'Import translations into database';

    private Repository $repository;
    private TranslatorBagInterface $translatorBag;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Repository $repository,
        TranslatorBagInterface $translatorBag,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->repository = $repository;
        $this->translatorBag = $translatorBag;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(Language::class);

        /** @var Language[] $languages */
        $languages = $repository->findAll();
        foreach ($languages as $language) {
            $this->repository->persistCatalogue($this->translatorBag->getCatalogue($language->getLocale()));
            $output->writeln('Imported catalogue ' . $language->getLocale());
        }

        return 0;
    }
}

