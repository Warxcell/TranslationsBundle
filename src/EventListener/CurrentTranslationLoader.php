<?php

namespace ObjectBG\TranslationBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use ObjectBG\TranslationBundle\TranslatableInterface;
use ObjectBG\TranslationBundle\TranslationService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class CurrentTranslationLoader implements EventSubscriber {

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

    public function __construct(Container $Container) {
        $this->Container = $Container;
        $this->PropertyAccess = PropertyAccess::createPropertyAccessor();
    }

    public function getSubscribedEvents() {
        return array('postLoad');
    }

    public function postLoad($Event) {
        $Entity = $Event->getEntity();
        if (!$Entity instanceof TranslatableInterface) {
            return;
        }

        /* @var $TranslationService TranslationService */
        $TranslationService = $this->Container->get('object_bg.translation.service.translation');

        $CurrentLanguage = $TranslationService->getCurrentLanguage();

        $Translations = $this->PropertyAccess->getValue($Entity, $TranslationService->getTranslationsField($Entity));
        if (!$Translations) {
            return;
        }
        $PropertyAccess = $this->PropertyAccess;
        $CurrentTranslation = $Translations->filter(function($item) use ($TranslationService, $CurrentLanguage, $PropertyAccess) {
                    return $PropertyAccess->getValue($item, $TranslationService->getLanguageField($item)) == $CurrentLanguage;
                })->first();

        if ($CurrentTranslation) {
            $CurrentTranslationField = $TranslationService->getCurrentTranslationField($Entity);
            $this->PropertyAccess->setValue($Entity, $CurrentTranslationField, $CurrentTranslation);
        }
    }

}
