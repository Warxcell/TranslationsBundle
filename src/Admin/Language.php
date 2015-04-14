<?php

namespace ObjectBG\TranslationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\DependencyInjection\Container;

class Language extends Admin {

    private $Container;

    public function setContainer(Container $Container) {
        $this->Container = $Container;
    }

    /**
     * The base route name used to generate the routing information
     *
     * @var string
     */
    protected $baseRouteName = 'object_bg.translation_bundle.languages';

    /**
     * The base route pattern used to generate the routing information
     *
     * @var string
     */
    protected $baseRoutePattern = 'translation-bundle/languages';

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('locale')
                ->add('name')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('locale')
                ->add('name')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('locale')
                ->addIdentifier('name')
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                    )
                ))
        ;
    }

    public function postPersist($object) {
        $this->Container->get('object_bg.translation.helper')->addLanguageFile($object->getLocale());
    }

    public function postRemove($object) {
        $this->Container->get('object_bg.translation.helper')->removeLanguageFile($object->getLocale());
    }

    public function preUpdate($object) {
        $this->Container->get('object_bg.translation.helper')->removeLanguageFile($object->getLocale());
    }

    public function postUpdate($object) {
        $this->Container->get('object_bg.translation.helper')->addLanguageFile($object->getLocale());
    }

}
