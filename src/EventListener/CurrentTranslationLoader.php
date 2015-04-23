<?php

namespace ObjectBG\TranslationBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use ObjectBG\TranslationBundle\TranslatableInterface;
use ObjectBG\TranslationBundle\TranslationService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class CurrentTranslationLoader implements EventSubscriber
{

    /**
     *
     * @var Container
     */
    private $Container;

    /**
     *
     * @var PropertyAccessor
     */
    private $PropertyAccess;
    private $Fallback = true;

    public function __construct(Container $Container)
    {
        $this->Container      = $Container;
        $this->PropertyAccess = PropertyAccess::createPropertyAccessor();
    }

    public function doFallback($trueFalse)
    {
        $this->Fallback = $trueFalse;
    }

    public function getSubscribedEvents()
    {
        return array('postLoad');
    }

    public function postLoad($Event)
    {
        $Entity = $Event->getEntity();
        if (!$Entity instanceof TranslatableInterface) {
            return;
        }

        $this->initializeCurrentTranslation($Entity);
    }

    public function initializeCurrentTranslation($Entity)
    {
        $TranslationService = $this->Container->get('object_bg.translation.service.translation');
        $CurrentLanguage    = $TranslationService->getCurrentLanguage();
        $success            = $this->initializeTranslation($Entity, $CurrentLanguage);

        if ($success == false && $this->Fallback === true) {
            $this->initializeFallbackTranslation($Entity);
        }
    }

    private function initializeFallbackTranslation($Entity)
    {
        $TranslationService = $this->Container->get('object_bg.translation.service.translation');
        $fallbacks          = $TranslationService->getFallbackLocales();

        foreach ($fallbacks as $fallback) {
            if ($this->initializeTranslation($Entity, $fallback)) {
                break;
            }
        }
    }

    public function initializeTranslation($Entity, $Language)
    {
        if (!$Entity instanceof TranslatableInterface) {
            throw new \RuntimeException('Entity is not translatable');
        }

        $TranslationService = $this->Container->get('object_bg.translation.service.translation');

        $Translations = $this->PropertyAccess->getValue($Entity, $TranslationService->getTranslationsField($Entity));

        if (!$Translations) {
            return false;
        }
        $PropertyAccess = $this->PropertyAccess;

        $CurrentTranslation = $Translations->filter(function($item) use ($TranslationService, $Language, $PropertyAccess) {
                    $TranslationLanguage = $PropertyAccess->getValue($item, $TranslationService->getLanguageField($item));
                    return $Language instanceof \ObjectBG\TranslationBundle\Entity\Language ? ($TranslationLanguage == $Language) : ($TranslationLanguage->getLocale() == $Language);
                })->first();

        if (!$CurrentTranslation) {
            return false;
        }
        $CurrentTranslationField = $TranslationService->getCurrentTranslationField($Entity);
        $this->PropertyAccess->setValue($Entity, $CurrentTranslationField, $CurrentTranslation);
        return true;
    }

}
