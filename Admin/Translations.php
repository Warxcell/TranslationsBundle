<?php

namespace ObjectBG\TranslationBundle\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\Admin as AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class Translations extends AbstractAdmin
{
    /**
     * The base route name used to generate the routing information
     *
     * @var string
     */
    protected $baseRouteName = 'object_bg.translation_bundle.translations';

    /**
     * The base route pattern used to generate the routing information
     *
     * @var string
     */
    protected $baseRoutePattern = 'translation-bundle/translations';
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    );
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**n
     * @param $em EntityManagerInterface
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('catalogue', 'hidden', array('data' => 'messages'))
            ->add('translationToken')
            ->add('language')
            ->add('translation');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('translation')
            ->add('language')
            ->add('translationToken');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'translation',
                null,
                array(
                    'edit' => 'inline',
                )
            )
            ->add('language')
            ->add('translationToken')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                    ),
                )
            );
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
    }

    /**
     * @return VM5TranslationSourceIterator
     */
    public function getDataSourceIterator()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('token')
            ->from('ObjectBGTranslationBundle:TranslationToken', 'token')
            ->orderBy('token.id')
            ->getQuery();
        $languages = $this->em->createQuery(
            'SELECT lang FROM ObjectBGTranslationBundle:Language lang INDEX BY lang.id'
        )->getResult();

        return new VM5TranslationSourceIterator($qb, $languages);
    }
}
