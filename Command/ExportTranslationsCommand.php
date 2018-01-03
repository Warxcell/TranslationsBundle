<?php

namespace ObjectBG\TranslationBundle\Command;

use ObjectBG\TranslationBundle\Entity\Language;
use ObjectBG\TranslationBundle\Entity\TranslationToken;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\MessageCatalogue;

class ExportTranslationsCommand extends ContainerAwareCommand
{

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;
    private $io;

    protected function configure()
    {
        $this
            ->setName('objectbg:translation:export')
            ->setDefinition(
                array(
                    new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
                    new InputArgument(
                        'bundle',
                        InputArgument::OPTIONAL,
                        'The bundle name or directory where to load the messages, defaults to app/Resources folder'
                    ),
                    new InputOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Force the output format.', 'xlf'),
                    new InputOption('clean', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE),
                )
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);
        $kernel = $this->getContainer()->get('kernel');

        $format = $this->input->getOption('format');
        $clean = $this->input->getOption('clean');
        $locale = $this->input->getArgument('locale');

        $bundleName = $this->input->getArgument('bundle');
        if ($bundleName) {
            $bundle = $kernel->getBundle($bundleName);
            $transPaths = $bundle->getPath() . '/Resources/translations';
        }
        $this->exportFile($transPaths, $locale, $format, $clean);
    }

    private function exportFile($transPaths, $locale, $format, $clean)
    {
        $db = $this->exportFromDB($locale);
        $language = $this->getContainer()->get('doctrine')->getManager()->getRepository(Language::class)->findOneBy(
            ['locale' => $locale]
        );
        $currentCatalogue = $this->extractMessages($locale, $transPaths);
        $extractedCatalogue = new MessageCatalogue($locale);
        if ($db != null) {
            foreach ($db as $token) {
                $translation = $token->getTranslation($language)->getTranslation();
                if (!$translation) {
                    $translation = $token->getToken();
                }
                $extractedCatalogue->set($token->getToken(), $translation, $token->getCatalogue());
            }
        } else {
            $this->output->writeln('<comment>No translations to export.</comment>');

            return;
        }
        $writer = $this->getContainer()->get('translation.writer');
        $supportedFormats = $writer->getFormats();
        if (!in_array($format, $supportedFormats)) {
            $this->io->error(
                array('Wrong output format', 'Supported formats are: ' . implode(', ', $supportedFormats) . '.')
            );

            return 1;
        }

        $operation = $clean ? new TargetOperation($currentCatalogue, $extractedCatalogue) : new MergeOperation(
            $currentCatalogue, $extractedCatalogue
        );

        $writer->writeTranslations(
            $operation->getResult(),
            $format,
            array(
                'path' => $transPaths,
                'default_locale' => $this->getContainer()->getParameter('kernel.default_locale'),
            )
        );
    }

    /**
     *
     * @param string $locale
     * @return TranslationToken[]
     */
    private function exportFromDB($locale)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        return $em->getRepository(TranslationToken::class)->getAllTokensByLocale($locale);
    }

    /**
     *
     * @param type $locale
     * @param type $transPaths
     * @return MessageCatalogue
     */
    private function extractMessages($locale, $transPaths)
    {
        /** @var TranslationLoader $loader */
        $loader = $this->getContainer()->get('translation.loader');
        $currentCatalogue = new MessageCatalogue($locale);

        if (is_dir($transPaths)) {
            $loader->loadMessages($transPaths, $currentCatalogue);
        }

        return $currentCatalogue;
    }

}
