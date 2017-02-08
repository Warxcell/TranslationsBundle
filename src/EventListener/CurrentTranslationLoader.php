<?php

namespace ObjectBG\TranslationBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use ObjectBG\TranslationBundle\Entity\Language;
use ObjectBG\TranslationBundle\TranslatableInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class CurrentTranslationLoader implements EventSubscriber
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var bool
     */
    private $fallback = true;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function doFallback($trueFalse)
    {
        $this->fallback = $trueFalse;
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
        $translationService = $this->container->get('object_bg.translation.service.translation');
        $CurrentLanguage = $translationService->getCurrentLanguage();
        $success = $this->initializeTranslation($Entity, $CurrentLanguage);

        if ($success == false && $this->fallback === true) {
            $this->initializeFallbackTranslation($Entity);
        }
    }

    private function initializeFallbackTranslation($Entity)
    {
        $translationService = $this->container->get('object_bg.translation.service.translation');
        $fallbacks = $translationService->getFallbackLocales();

        foreach ($fallbacks as $fallback) {
            if ($this->initializeTranslation($Entity, $fallback)) {
                break;
            }
        }
    }

    public function initializeTranslation($entity, $languageOrLocale)
    {
        if (!$entity instanceof TranslatableInterface) {
            throw new \RuntimeException('Entity is not translatable');
        }

        $translationService = $this->container->get('object_bg.translation.service.translation');

        $translations = $this->propertyAccessor->getValue($entity, $translationService->getTranslationsField($entity));

        if (!$translations) {
            return false;
        }
        $propertyAccessor = $this->propertyAccessor;

        $currentTranslation = $translations->filter(
            function ($item) use ($translationService, $languageOrLocale, $propertyAccessor) {
                $translationLanguage = $propertyAccessor->getValue($item, $translationService->getLanguageField($item));

                if ($languageOrLocale instanceof Language) {
                    return $translationLanguage == $languageOrLocale;
                } else {
                    $translationLanguage->getLocale() == $languageOrLocale;
                }
            }
        )->first();

        if (!$currentTranslation) {
            return false;
        }
        $currentTranslationField = $translationService->getCurrentTranslationField($entity);
        $this->propertyAccessor->setValue($entity, $currentTranslationField, $currentTranslation);

        return true;
    }
}
