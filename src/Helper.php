<?php

namespace ObjectBG\TranslationBundle;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpKernel\Kernel;

class Helper {

    /**
     *
     * @var Kernel
     */
    private $Kernel;

    /**
     *
     * @var Filesystem
     */
    private $FileSystem;

    public function __construct(\Symfony\Component\DependencyInjection\Container $Container) {
        $this->Kernel = $Container->get('kernel');
        $this->FileSystem = new Filesystem;
    }

    public function clearTranslationCache() {
        $dirPath = $this->Kernel->getCacheDir() . '/translations/';
        $this->FileSystem->remove($dirPath);
    }

    private function getLanguageFile($locale, $domain = 'messages') {
        $file = $this->Kernel->getRootDir() . '/Resources/translations/' . $domain . '.' . $locale . '.db';
        return $file;
    }

    public function addLanguageFile($locale, $domain = 'messages') {
        $file = $this->getLanguageFile($locale, $domain);
        $dir = dirname($file);
        if (!$this->FileSystem->exists($dir)) {
            $this->FileSystem->mkdir($dir);
        }
        $this->FileSystem->touch($file);

        $this->clearTranslationCache();
    }

    public function removeLanguageFile($locale, $domain = 'messages') {
        $file = $this->getLanguageFile($locale, $domain);
        $this->FileSystem->remove($file);
        
        $this->clearTranslationCache();
    }

}
