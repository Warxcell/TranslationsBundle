<?php

namespace ObjectBG\TranslationBundle;

class Helper {

	private $Kernel;

	public function __construct(\Symfony\Component\DependencyInjection\Container $Container) {
		$this->Kernel = $Container->get('kernel');
	}

	public function clearTranslationCache() {
		$dirPath = $this->Kernel->getRootDir() . '/cache/' . $this->Kernel->getEnvironment() . '/translations/';
		if (!is_dir($dirPath)) {
			throw new \InvalidArgumentException("$dirPath must be a directory");
		}
		if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
			$dirPath .= '/';
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			unlink($file);
		}
		rmdir($dirPath);
	}

	public function addLanguageFile($locale) {
		/**
		 * @todo
		 */
	}

}
