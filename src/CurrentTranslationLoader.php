<?php

namespace ObjectBG\TranslationBundle;

use ObjectBG\TranslationBundle\Entity\Language;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class CurrentTranslationLoader
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
    private $managedEntities = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param $trueFalse
     */
    public function doFallback($trueFalse)
    {
        $this->fallback = $trueFalse;
    }

    /**
     * Reinitialize all current translations of all managed entities
     */
    public function flush()
    {
        foreach ($this->managedEntities as $entity) {
            $this->initializeCurrentTranslation($entity);
        }
    }

    /**
     * @param TranslatableInterface $entity
     */
    public function initializeCurrentTranslation(TranslatableInterface $entity)
    {
        $translationService = $this->container->get('object_bg.translation.service.translation');
        $CurrentLanguage = $translationService->getCurrentLanguage();
        $success = $this->initializeTranslation($entity, $CurrentLanguage);

        if ($success == false && $this->fallback === true) {
            $this->initializeFallbackTranslation($entity);
        }
    }

    /**
     * @param TranslatableInterface $entity
     */
    private function initializeFallbackTranslation(TranslatableInterface $entity)
    {
        $translationService = $this->container->get('object_bg.translation.service.translation');
        $fallbackLocales = $translationService->getFallbackLocales();

        foreach ($fallbackLocales as $fallbackLocale) {
            if ($this->initializeTranslation($entity, $fallbackLocale)) {
                break;
            }
        }
    }

    /**
     * @param TranslatableInterface $entity
     * @param $languageOrLocale
     * @return bool
     */
    public function initializeTranslation(TranslatableInterface $entity, $languageOrLocale)
    {
        $oid = spl_object_hash($entity);
        $this->managedEntities[$oid] = $entity;

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
