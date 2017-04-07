<?php

namespace ObjectBG\TranslationBundle\Form\Type;

use ObjectBG\TranslationBundle\Form\EventListener\TranslationsListener;
use ObjectBG\TranslationBundle\TranslationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationsType extends AbstractType
{

    private $translationsListener;
    private $TranslationService;

    /**
     *
     * @param \A2lix\TranslationFormBundle\Form\EventListener\TranslationsListener $translationsListener
     * @param array $locales
     * @param string $defaultLocale
     * @param array $requiredLocales
     */
    public function __construct(TranslationsListener $translationsListener, TranslationService $TranslationService)
    {
        $this->translationsListener = $translationsListener;
        $this->TranslationService = $TranslationService;
    }

    /**
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->translationsListener);
    }

    /**
     *
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['default_locale'] = $options['default_locale'];
        $view->vars['required_locales'] = $options['required_locales'];
    }

    /**
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'by_reference' => false,
                'empty_data' => new \Doctrine\Common\Collections\ArrayCollection(),
                'locales' => $this->TranslationService->getLocales(),
                'default_locale' => $this->TranslationService->getDefaultLocale(),
                'required_locales' => $this->TranslationService->getRequiredLocales(),
                'translation_class' => null,
                'fields' => array(),
                'exclude_fields' => array(),
            )
        );
    }

}
