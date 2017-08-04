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
     * @var array
     */
    private $managedEntities = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
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

    public function detach(TranslatableInterface $translatable)
    {
        unset($this->managedEntities[$this->getId($translatable)]);
    }

    /**
     * @param TranslatableInterface $entity
     */
    public function initializeCurrentTranslation(TranslatableInterface $entity)
    {
        /** @var TranslationService $translationService */
        $translationService = $this->container->get('object_bg.translation.service.translation');
        /** @var Language $currentLanguage */
        $currentLanguage = $translationService->getCurrentLanguage();
        $success = $this->initializeTranslation($entity, $currentLanguage);

        $locale = $currentLanguage->getLocale();

        if ($success == false) {
            $locale = $this->initializeFallbackTranslation($entity);
        }

        return $locale;
    }

    /**
     * @param TranslatableInterface $entity
     */
    private function initializeFallbackTranslation(TranslatableInterface $entity)
    {
        /** @var TranslationService $translationService */
        $translationService = $this->container->get('object_bg.translation.service.translation');
        $fallbackLocales = $translationService->getFallbackLocales();

        foreach ($fallbackLocales as $fallbackLocale) {
            if ($this->initializeTranslation($entity, $fallbackLocale)) {
                return $fallbackLocale;
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
        $this->managedEntities[$this->getId($entity)] = $entity;

        /** @var TranslationService $translationService */
        $translationService = $this->container->get('object_bg.translation.service.translation');

        $currentTranslation = $this->getTranslation($entity, $languageOrLocale);
        if ($currentTranslation) {
            $currentTranslationField = $translationService->getCurrentTranslationField($entity);
            $this->propertyAccessor->setValue($entity, $currentTranslationField, $currentTranslation);

            return true;
        }

        return false;
    }

    public function getTranslation(TranslatableInterface $translatable, $languageOrLocale)
    {
        /** @var TranslationService $translationService */
        $translationService = $this->container->get('object_bg.translation.service.translation');

        $translations = $this->propertyAccessor->getValue(
            $translatable,
            $translationService->getTranslationsField($translatable)
        );

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
            return null;
        }

        return $currentTranslation;
    }

    private function getId(TranslatableInterface $translatable)
    {
        return spl_object_hash($translatable);
    }
}
