<?php

namespace ObjectBG\TranslationBundle\Form\Type;

use A2lix\TranslationFormBundle\Form\EventListener\TranslationsFormsListener;
use A2lix\TranslationFormBundle\TranslationForm\TranslationForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author David ALLIX
 */
class TranslationsFormsType extends AbstractType
{

    private $translationForm;
    private $translationsListener;
    private $locales;
    private $defaultLocale;
    private $requiredLocales;

    /**
     *
     * @param \A2lix\TranslationFormBundle\TranslationForm\TranslationForm $translationForm
     * @param \A2lix\TranslationFormBundle\Form\EventListener\TranslationsFormsListener $translationsListener
     * @param array $locales
     * @param string $defaultLocale
     * @param array $requiredLocales
     */
    public function __construct(
        TranslationForm $translationForm,
        TranslationsFormsListener $translationsListener,
        array $locales,
        $defaultLocale,
        array $requiredLocales = array()
    ) {
        $this->translationForm = $translationForm;
        $this->translationsListener = $translationsListener;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
        $this->requiredLocales = $requiredLocales;
    }

    /**
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->translationsListener);

        $formsOptions = $this->translationForm->getFormsOptions($options);
        foreach ($options['locales'] as $locale) {
            if (isset($formsOptions[$locale])) {
                $builder->add(
                    $locale,
                    $options['form_type'],
                    $formsOptions[$locale] + array('required' => in_array($locale, $options['required_locales']))
                );
            }
        }
    }

    /**
     *
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['default_locale'] = $this->defaultLocale;
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
                'locales' => $this->locales,
                'required_locales' => $this->requiredLocales,
                'form_type' => null,
                'form_options' => array(),
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'object_bg_translations_forms';
    }
}
