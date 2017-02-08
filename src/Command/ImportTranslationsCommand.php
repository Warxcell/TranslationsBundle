<?php

namespace ObjectBG\TranslationBundle\Command;

use ObjectBG\TranslationBundle\Entity\Language;
use ObjectBG\TranslationBundle\Entity\Translation;
use ObjectBG\TranslationBundle\Entity\TranslationRepository;
use ObjectBG\TranslationBundle\Entity\TranslationToken;
use ObjectBG\TranslationBundle\Entity\TranslationTokenRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\MessageCatalogue;

class ImportTranslationsCommand extends ContainerAwareCommand
{

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('objectbg:translation:import')
            ->setDefinition(
                array(
                    new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
                    new InputArgument(
                        'bundle',
                        InputArgument::OPTIONAL,
                        'The bundle name or directory where to load the messages, defaults to app/Resources folder'
                    ),
                    new InputOption('all', null, InputOption::VALUE_NONE, 'Load messages from all registered bundles'),
                    new InputOption('override', null, InputOption::VALUE_NONE, 'Should the update be done'),
                )
            )
            ->setDescription('Displays translation messages information')
            ->setHelp(
                <<<'EOF'
                The <info>%command.name%</info> command helps finding unused or missing translation
messages and comparing them with the fallback ones by inspecting the
templates and translation files of a given bundle or the app folder.
You can display information about bundle translations in a specific locale:
  <info>php %command.full_name% en AcmeDemoBundle</info>
You can also specify a translation domain for the search:
  <info>php %command.full_name% --domain=messages en AcmeDemoBundle</info>
You can display information about app translations in a specific locale:
  <info>php %command.full_name% en</info>
You can display information about translations in all registered bundles in a specific locale:
  <info>php %command.full_name% --all en</info>
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $kernel = $this->getContainer()->get('kernel');
        $transPaths = array($kernel->getRootDir() . '/Resources/');

        $locale = $this->input->getArgument('locale');
        $override = $this->input->getOption('override');


        $bundleName = $this->input->getArgument('bundle');
        if ($bundleName) {
            $bundle = $kernel->getBundle($bundleName);
            $transPaths[] = $bundle->getPath() . '/Resources/';
            $transPaths[] = sprintf('%s/Resources/%s/', $kernel->getRootDir(), $bundle->getName());
        } elseif ($input->getOption('all')) {
            foreach ($kernel->getBundles() as $bundle) {
                $transPaths[] = $bundle->getPath() . '/Resources/';
                $transPaths[] = sprintf('%s/Resources/%s/', $kernel->getRootDir(), $bundle->getName());
            }
        }

        $catalogue = $this->extractMessages($locale, $transPaths);
        $this->importTranslationFiles($catalogue, $locale, $override);
    }

    /**
     * @param string $locale
     * @param array $transPaths
     *
     * @return MessageCatalogue
     */
    private function extractMessages($locale, $transPaths)
    {
        /** @var TranslationLoader $loader */
        $loader = $this->getContainer()->get('translation.loader');

        $currentCatalogue = new MessageCatalogue($locale);
        foreach ($transPaths as $path) {
            $path = $path . 'translations';
            if (is_dir($path)) {
                $loader->loadMessages($path, $currentCatalogue);
            }
        }

        return $currentCatalogue;
    }

    public function importTranslationFiles(MessageCatalogue $messages, $locale, $override)
    {
        $domains = $messages->all();
        $translationToken = null;
        $translation = null;

        $em = $this->getContainer()->get('doctrine')->getManager();
        $language = $em->getRepository(Language::class)->findOneBy(['locale' => $locale]);

        /** @var TranslationTokenRepository $transTokenRepo */
        $transTokenRepo = $em->getRepository(TranslationToken::class);
        /** @var TranslationRepository $transRepo */
        $transRepo = $em->getRepository(Translation::class);

        foreach ($domains as $catalogue => $messages) {
            foreach ($messages as $token => $val) {
                $translationToken = $transTokenRepo->findByTokenAndCatalogue($token, $catalogue);
                if (!$translationToken) {
                    $translationToken = new TranslationToken();
                    $translationToken->setToken($token);
                    $translationToken->setCatalogue($catalogue);
                } else {
                    $translation = $transRepo->getTranslationByTokenAndLanguage($translationToken, $language);
                }

                if (!$translation || $override) {
                    if (!$translation) {
                        $translation = new Translation();
                    }
                    $translation->setLanguage($language);
                    $translation->setTranslationToken($translationToken);
                    $translation->setTranslation($val);
                    $em->persist($translationToken);
                    $em->persist($translation);
                }
            }
        }
        $em->flush();
    }

}
