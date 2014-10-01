<?php

namespace ObjectBG\TranslationBundle\Form\Type;

use Symfony\Component\Form\FormView,
    Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormInterface,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolverInterface,
    ObjectBG\TranslationBundle\Form\EventListener\TranslationsListener,
    ObjectBG\TranslationBundle\TranslationService;

class TranslationsType extends AbstractType {

    private $translationsListener;
    private $TranslationService;

    /**
     *
     * @param \A2lix\TranslationFormBundle\Form\EventListener\TranslationsListener $translationsListener
     * @param array $locales
     * @param string $defaultLocale
     * @param array $requiredLocales
     */
    public function __construct(TranslationsListener $translationsListener, TranslationService $TranslationService) {
        $this->translationsListener = $translationsListener;
        $this->TranslationService = $TranslationService;
    }

    /**
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventSubscriber($this->translationsListener);
    }

    /**
     *
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options) {
        $view->vars['default_locale'] = $options['default_locale'];
        $view->vars['required_locales'] = $options['required_locales'];
    }

    /**
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'by_reference' => false,
            'empty_data' => new \Doctrine\Common\Collections\ArrayCollection(),
            'locales' => $this->TranslationService->getLocales(),
            'default_locale' => $this->TranslationService->getDefaultLocale(),
            'required_locales' => $this->TranslationService->getRequiredLocales(),
            'fields' => array(),
            'exclude_fields' => array(),
        ));
    }

    public function getName() {
        return 'object_bg_translations';
    }

}
