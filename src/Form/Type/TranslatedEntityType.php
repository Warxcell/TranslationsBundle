<?php

namespace ObjectBG\TranslationBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Translated entity
 *
 * @author David ALLIX
 */
class TranslatedEntityType extends AbstractType
{
    /**
     * @var Request
     */
    private $request;

    public function setRequest(RequestStack $request = null)
    {
        $this->request = $request->getCurrentRequest();
    }

    /**
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_path' => 'translations',
                'translation_property' => null,
                'property' => function (Options $options) {
                    if (null === $this->request) {
                        throw new \Exception('Error while getting request');
                    }

                    return $options['translation_path'].'['.$this->request->getLocale(
                        ).'].'.$options['translation_property'];
                },
            )
        );
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function getBlockPrefix()
    {
        return 'object_bg_translated_entity';
    }
}
