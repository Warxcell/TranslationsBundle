<?php

namespace ObjectBG\TranslationBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent,
    Symfony\Component\Form\FormEvents,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    ObjectBG\TranslationBundle\TranslationService,
    Symfony\Component\PropertyAccess\PropertyAccess,
    Symfony\Component\PropertyAccess\PropertyAccessor;

class TranslationsListener implements EventSubscriberInterface {

    /**
     *
     * @var TranslationService
     */
    private $TranslationService;

    /**
     *
     * @var PropertyAccessor
     */
    private $PropertyAccess;

    /**
     * 
     * @param TranslationService $TranslationService
     */
    public function __construct(TranslationService $TranslationService) {
        $this->TranslationService = $TranslationService;
        $this->PropertyAccess = PropertyAccess::createPropertyAccessor();
    }

    /**
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function preSetData(FormEvent $event) {
        $form = $event->getForm();

        $data = $event->getData();
        if ($data) {
            $newData = array();
            foreach ($data as $each) {
                $newData[$each->getLanguage()->getLocale()] = $each;
            }
            $event->setData($newData);
        }

        $translationClass = $form->getConfig()->getOption('translation_class');
        if ($translationClass === null) {
            $translatableClass = $form->getParent()->getConfig()->getDataClass();
            $translationClass = $this->TranslationService->getTranslationClass($translatableClass);
        }

        $formOptions = $form->getConfig()->getOptions();

        $fieldsOptions = $this->TranslationService->getFieldsOptions($translationClass, $formOptions);

        foreach ($formOptions['locales'] as $locale => $name) {
            if (isset($fieldsOptions[$locale])) {
                $form->add($locale, 'object_bg_translation_fields', array(
                    'label' => $name,
                    'data_class' => $translationClass,
                    'fields' => $fieldsOptions[$locale],
                    'required' => in_array($locale, $formOptions['required_locales'])
                ));
            }
        }
    }

    /**
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function submit(FormEvent $event) {
        $data = $event->getData();

        $Translatable = $event->getForm()->getParent()->getData();
        foreach ($data as $locale => $translation) {
            // Remove useless Translation object
            if (!$translation) {
                $Translatable->getTranslations()->removeElement($translation);
				unset($data[$locale]);
            } else {
                $LanguageField = $this->TranslationService->getLanguageField(get_class($translation));
                $TranslatableField = $this->TranslationService->getTranslatableField(get_class($translation));

                $Language = $this->TranslationService->getLanguageByLocale($locale);
                $this->PropertyAccess->setValue($translation, $LanguageField, $Language);
                $this->PropertyAccess->setValue($translation, $TranslatableField, $Translatable);
            }
        }
		$event->setData($data);
    }

    public static function getSubscribedEvents() {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::SUBMIT => 'submit',
        );
    }

}
