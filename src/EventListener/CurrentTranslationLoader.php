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
    /**
     * @var array
     */
    private $managed = [];

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

    public function flush()
    {
        foreach ($this->managed as $entity) {
            $this->initializeCurrentTranslation($entity);
        }
    }

    public function postLoad($event)
    {
        $Entity = $event->getEntity();
        if (!$Entity instanceof TranslatableInterface) {
            return;
        }

        $this->initializeCurrentTranslation($Entity);
    }

    public function initializeCurrentTranslation($entity)
    {
        $translationService = $this->container->get('object_bg.translation.service.translation');
        $CurrentLanguage = $translationService->getCurrentLanguage();
        $success = $this->initializeTranslation($entity, $CurrentLanguage);

        if ($success == false && $this->fallback === true) {
            $this->initializeFallbackTranslation($entity);
        }
    }

    private function initializeFallbackTranslation($entity)
    {
        $translationService = $this->container->get('object_bg.translation.service.translation');
        $fallbacks = $translationService->getFallbackLocales();

        foreach ($fallbacks as $fallback) {
            if ($this->initializeTranslation($entity, $fallback)) {
                break;
            }
        }
    }

    public function initializeTranslation($entity, $languageOrLocale)
    {
        if (!$entity instanceof TranslatableInterface) {
            throw new \RuntimeException('Entity is not translatable');
        }
        $oid = spl_object_hash($entity);
        $this->managed[$oid] = $entity;

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
                    return $translationLanguage->getLocale() == $languageOrLocale;
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
