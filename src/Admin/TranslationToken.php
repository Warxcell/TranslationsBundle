<?php

namespace ObjectBG\TranslationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityManager;

class TranslationToken extends Admin
{

    /**
     * The base route name used to generate the routing information
     *
     * @var string
     */
    protected $baseRouteName = 'object_bg.translation_bundle.translations_tokens';

    /**
     * The base route pattern used to generate the routing information
     *
     * @var string
     */
    protected $baseRoutePattern = 'translation-bundle/translations-tokens';

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * 
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('token')
            ->add('translations', 'sonata_type_collection', array(
                'type_options' => array(
                    'delete' => false,
                    'required' => false,
                ),
                'btn_add' => false
                ), array(
                'edit' => 'inline',
                'inline' => 'table',
                'admin_code' => 'objectbg.admin.token_translation'
            ))
        ;

        $languages = $this->em->getRepository('ObjectBGTranslationBundle:Language')->findAll();
        $languages = new \Doctrine\Common\Collections\ArrayCollection($languages);

        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::PRE_SET_DATA, function(FormEvent $Event) use ($languages) {
            $data = $Event->getData();
            if ($data) {
                $translations = $data->getTranslations();

                $langs = array();
                foreach ($translations as $trans) {
                    $langs[] = $trans->getLanguage();
                }
                $missingLanguages = $languages->filter(function($item) use ($langs) {
                    return array_search($item, $langs) === false;
                });

                foreach ($missingLanguages as $lang) {
                    $newTranslation = new \ObjectBG\TranslationBundle\Entity\Translation();
                    $newTranslation->setLanguage($lang);
                    $data->getTranslations()->add($newTranslation);
                }
            }
        });

        $subject = $this->getSubject();
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::POST_SUBMIT, function(FormEvent $Event) use ($subject) {
            $data = $Event->getData();
            foreach ($data->getTranslations() as $translation) {
                if ($translation->getTranslation() == null) {
                    $data->getTranslations()->removeElement($translation);
                } else {
                    $translation->setCatalogue('messages');
                    $translation->setTranslationToken($subject);
                }
            }
        });
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('token')
        ;

        $datagridMapper
            ->add('show-only-untranslated', 'doctrine_orm_callback', array(
                'label' => 'Show only untranslated',
                'callback' => function ($queryBuilder, $alias, $field, $value) {
                    if ($value['value'] == null) {
                        return;
                    }
                    $subQuery = 'SELECT COUNT(lang) FROM ObjectBGTranslationBundle:Language lang';
                    $queryBuilder->andWhere(sprintf('SIZE(%s.translations) < (%s)', $alias, $subQuery));
                },
                'field_type' => 'checkbox'
        ));
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('token')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }
}
