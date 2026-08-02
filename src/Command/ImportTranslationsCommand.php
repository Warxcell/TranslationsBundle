<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle\Command;

use Arxy\TranslationsBundle\Model\Language;
use Arxy\TranslationsBundle\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\TranslatorBagInterface;
use Throwable;

use function sprintf;

#[AsCommand('arxy:translations:import-translations', 'Import translations into database')]
class ImportTranslationsCommand extends Command
{
    public function __construct(
        private readonly Repository $repository,
        private readonly TranslatorBagInterface $translatorBag,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('catalogue', 'c', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED);
    }

    /**
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(Language::class);

        $this->entityManager->beginTransaction();

        try {
            /** @var Language[] $languages */
            $languages = $repository->findAll();

            foreach ($languages as $language) {
                $finalCatalogue = $this->translatorBag->getCatalogue($language->getLocale());

                if ($input->hasOption('catalogue')) {
                    $catalogue = $finalCatalogue;
                    $finalCatalogue = new MessageCatalogue($language->getLocale());
                    foreach ($input->getOption('catalogue') as $catalogueAsString) {
                        $output->writeln(
                            sprintf(
                                'Importing from language %s catalogue %s',
                                $language->getLocale(),
                                $catalogueAsString
                            )
                        );

                        $finalCatalogue->add($catalogue->all($catalogueAsString), $catalogueAsString);
                    }
                }

                $output->writeln(sprintf('Importing from language %s', $language->getLocale()));

                $this->repository->persistCatalogue($finalCatalogue);
            }

            $this->entityManager->commit();
        } catch (Throwable $throwable) {
            $this->entityManager->rollback();
            throw $throwable;
        }

        return 0;
    }
}
