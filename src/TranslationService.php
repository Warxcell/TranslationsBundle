<?php

namespace ObjectBG\TranslationBundle;

use Symfony\Component\Form\FormRegistry,
    Doctrine\Common\Persistence\ManagerRegistry,
    Doctrine\Common\Util\ClassUtils,
    Doctrine\Common\Annotations\Reader,
    Symfony\Component\DependencyInjection\Container,
    ObjectBG\TranslationBundle\Entity\Language,
    Symfony\Component\Translation\Translator,
    Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class TranslationService
{

    private $typeGuesser;

    /**
     *
     * @var ManagerRegistry 
     */
    private $managerRegistry;

    /**
     *
     * @var Reader
     */
    private $AnnotationReader;

    /**
     *
     * @var Container
     */
    private $Container;

    /**
     *
     * @var <Language>
     */
    private $Languages;

    /**
     *
     * @var Translator
     */
    private $Translator;

    /**
     *
     * @var Request
     */
    private $Request;

    /**
     *
     * @var PropertyAccessor
     */
    private $PropertyAccess;

    /**
     * 
     * @param \Symfony\Component\DependencyInjection\Container $Container
     * @param \Symfony\Component\Form\FormRegistry $formRegistry
     * @param \Doctrine\Common\Persistence\ManagerRegistry $managerRegistry
     * @param \Doctrine\Common\Annotations\Reader $Reader
     */
    public function __construct(Container $Container)
    {
        $this->Container        = $Container;
        $this->typeGuesser      = $Container->get('form.registry')->getTypeGuesser();
        $this->managerRegistry  = $Container->get('doctrine');
        $this->AnnotationReader = $Container->get('annotation_reader');
        $this->Translator       = $Container->get('translator');
        $this->Request          = $Container->get('request');
        $this->PropertyAccess   = PropertyAccess::createPropertyAccessor();
    }

    public function getTranslation($entity, $language)
    {
        $Translations = $this->PropertyAccess->getValue($entity, $this->getTranslationsField($entity));

        $translationService = $this;
        $PropertyAccess     = $this->PropertyAccess;
        $Translation        = $Translations->filter(function($item) use ($translationService, $language, $PropertyAccess) {
                    $TranslationLanguage = $PropertyAccess->getValue($item, $translationService->getLanguageField($item));
                    return $language instanceof Language ? ($TranslationLanguage == $language) : ($TranslationLanguage->getLocale() == $language);
                })->first();

        return $Translation;
    }

    public function getLanguages()
    {
        if (isset($this->Languages) == false) {
            $LanguageClass   = 'ObjectBG\TranslationBundle\Entity\Language';
            $manager         = $this->managerRegistry->getManagerForClass($LanguageClass);
            $this->Languages = $manager->getRepository($LanguageClass)->findAll();
            $this->Languages = new \Doctrine\Common\Collections\ArrayCollection($this->Languages);
        }
        return $this->Languages;
    }

    public function getCurrentLanguage()
    {
        $CurrentLocale = $this->Request->get('_locale');
        if (!$CurrentLocale) {
            $CurrentLocale = $this->Request->getLocale();
        }
        if (!$CurrentLocale) {
            $CurrentLocale = $this->Translator->getLocale();
        }
        return $this->getLanguages()->filter(function(Language $Lang) use ($CurrentLocale) {
                    return $Lang->getLocale() == $CurrentLocale;
                })->first();
    }

    public function getLocales()
    {
        $locales = array();
        foreach ($this->getLanguages() as $lang) {
            $locales[$lang->getLocale()] = $lang->getName();
        }
        return $locales;
    }

    public function getDefaultLocale()
    {
        return $this->Container->getParameter('locale');
    }

    public function getRequiredLocales()
    {
        return array();
//        return array($this->Container->getParameter('locale'));
    }

    public function getTranslationClass($TranslatableClass)
    {
        $TranslatableClass = ClassUtils::getRealClass($TranslatableClass);

        if ($manager = $this->managerRegistry->getManagerForClass($TranslatableClass)) {
            $metadataClass = $manager->getMetadataFactory()->getMetadataFor($TranslatableClass);
            foreach ($metadataClass->reflFields as $Field => $Reflection) {
                $Annotation = $this->AnnotationReader->getPropertyAnnotation($Reflection, 'ObjectBG\TranslationBundle\Annotation\Translations');
                if ($Annotation) {
                    $AssocMapping = $metadataClass->associationMappings[$Field];
                    return $AssocMapping['targetEntity'];
                }
            }
        }
        throw Exception\InvalidArgumentException::missingTranslations($TranslatableClass);
    }

    /**
     *
     * @param string $translationClass
     * @param array  $exclude
     * @return array
     */
    protected function getTranslationFields($translationClass, array $exclude = array())
    {
        $fields           = array();
        $translationClass = ClassUtils::getRealClass($translationClass);
        $manager          = $this->managerRegistry->getManagerForClass($translationClass);

        if ($manager) {
            $metadataClass = $manager->getMetadataFactory()->getMetadataFor($translationClass);

            foreach ($metadataClass->reflFields as $Field => $Reflection) {
                $Annotation = $this->AnnotationReader->getPropertyAnnotation($Reflection, 'ObjectBG\TranslationBundle\Annotation\Column');
                if ($Annotation) {
                    $fields[] = $Field;
                }
            }
        }
        return $fields;
    }

    protected function getFieldByAnnotation($Class, $Annotation)
    {
        if (is_object($Class)) {
            $Class = get_class($Class);
        }

        $Class           = ClassUtils::getRealClass($Class);
        $ReflectionClass = new \ReflectionClass($Class);

        foreach ($ReflectionClass->getProperties() as $ReflectionProperty) {
            $Found = $this->AnnotationReader->getPropertyAnnotation($ReflectionProperty, $Annotation);
            if ($Found) {
                return $ReflectionProperty->getName();
            }
        }

        throw Exception\InvalidArgumentException::missingRequiredAnnotation($Class, $Annotation);
    }

    public function getLanguageField($TranslationClass)
    {
        return $this->getFieldByAnnotation($TranslationClass, 'ObjectBG\TranslationBundle\Annotation\Language');
    }

    public function getTranslatableField($TranslationClass)
    {
        return $this->getFieldByAnnotation($TranslationClass, 'ObjectBG\TranslationBundle\Annotation\Translatable');
    }

    public function getTranslationsField($TranslatableClass)
    {
        return $this->getFieldByAnnotation($TranslatableClass, 'ObjectBG\TranslationBundle\Annotation\Translations');
    }

    public function getCurrentTranslationField($TranslatableClass)
    {
        return $this->getFieldByAnnotation($TranslatableClass, 'ObjectBG\TranslationBundle\Annotation\CurrentTranslation');
    }

    public function getLanguageByLocale($locale)
    {
        return $this->getLanguages()->filter(function(Language $Lang) use ($locale) {
                    return $Lang->getLocale() == $locale;
                })->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldsOptions($class, $options)
    {
        $fieldsOptions = array();

        foreach ($this->getFieldsList($options, $class) as $field) {
            $fieldOptions = isset($options['fields'][$field]) ? $options['fields'][$field] : array();

            if (!isset($fieldOptions['display']) || $fieldOptions['display']) {
                $fieldOptions = $this->guessMissingFieldOptions($this->typeGuesser, $class, $field, $fieldOptions);

                // Custom options by locale
                if (isset($fieldOptions['locale_options'])) {
                    $localesFieldOptions = $fieldOptions['locale_options'];
                    unset($fieldOptions['locale_options']);

                    foreach ($options['locales'] as $locale => $name) {
                        $localeFieldOptions = isset($localesFieldOptions[$locale]) ? $localesFieldOptions[$locale] : array();
                        if (!isset($localeFieldOptions['display']) || $localeFieldOptions['display']) {
                            $fieldsOptions[$locale][$field] = $localeFieldOptions + $fieldOptions;
                        }
                    }

                    // General options for all locales
                } else {
                    foreach ($options['locales'] as $locale => $name) {
                        $fieldsOptions[$locale][$field] = $fieldOptions;
                    }
                }
            }
        }

        return $fieldsOptions;
    }

    /**
     * Combine formFields with translationFields. (Useful for upload field)
     */
    private function getFieldsList($options, $class)
    {
        $formFields = array_keys($options['fields']);

        if (count($formFields) != 0) {
            // Check existing
            foreach ($formFields as $field) {
                if (!property_exists($class, $field)) {
                    throw new \Exception("Field '" . $field . "' doesn't exist in " . $class);
                }
            }

            return $formFields;
        }
        return array_unique(array_merge($formFields, $this->getTranslationFields($class, $options['exclude_fields'])));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormsOptions($options)
    {
        $formsOptions = array();

        // Current options
        $formOptions = $options['form_options'];

        // Custom options by locale
        if (isset($formOptions['locale_options'])) {
            $localesFormOptions = $formOptions['locale_options'];
            unset($formOptions['locale_options']);

            foreach ($options['locales'] as $locale) {
                $localeFormOptions = isset($localesFormOptions[$locale]) ? $localesFormOptions[$locale] : array();
                if (!isset($localeFormOptions['display']) || $localeFormOptions['display']) {
                    $formsOptions[$locale] = $localeFormOptions + $formOptions;
                }
            }

            // General options for all locales
        } else {
            foreach ($options['locales'] as $locale) {
                $formsOptions[$locale] = $formOptions;
            }
        }

        return $formsOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function guessMissingFieldOptions($guesser, $class, $property, $options)
    {
        if (!isset($options['field_type']) && ($typeGuess = $guesser->guessType($class, $property))) {
            $options['field_type'] = $typeGuess->getType();
        }

        if (!isset($options['pattern']) && ($patternGuess = $guesser->guessPattern($class, $property))) {
            $options['pattern'] = $patternGuess->getValue();
        }

        if (!isset($options['max_length']) && ($maxLengthGuess = $guesser->guessMaxLength($class, $property))) {
            $options['max_length'] = $maxLengthGuess->getValue();
        }

        return $options;
    }

}
