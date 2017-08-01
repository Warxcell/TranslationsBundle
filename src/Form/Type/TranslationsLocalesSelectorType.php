<?php

namespace ObjectBG\TranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationsLocalesSelectorType extends AbstractType
{

    private $locales;
    private $defaultLocale;

    /**
     *
     * @param array $locales
     * @param string $defaultLocale
     */
    public function __construct()
    {

//    public function __construct(array $locales, $defaultLocale) {
//        $this->locales = $locales;
//        $this->defaultLocale = $defaultLocale;
    }

    /**
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'choices' => array_combine($this->locales, $this->locales),
                'expanded' => true,
                'multiple' => true,
                'attr' => array(
                    'class' => "a2lix_translationsLocalesSelector",
                ),
            )
        );
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'object_bg_locales_selector';
    }
}
