<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Command;

use Arxy\TranslationsBundle\Repository;
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

    public function __construct(Repository $repository, TranslatorBagInterface $translatorBag)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->translatorBag = $translatorBag;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->translatorBag->getCatalogues() as $catalogue) {
            $this->repository->persistCatalogue($catalogue);
        }

        return 0;
    }
}

