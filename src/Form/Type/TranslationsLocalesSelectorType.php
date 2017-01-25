<?php

namespace ObjectBG\TranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
        return 'choice';
    }

    public function getName()
    {
        return 'object_bg_locales_selector';
    }
}
