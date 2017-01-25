<?php

namespace ObjectBG\TranslationBundle\Controller;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CRUDController extends \Sonata\AdminBundle\Controller\CRUDController
{

    public function listAction(\Symfony\Component\HttpFoundation\Request $request = null)
    {
        $canEdit = $this->admin->isGranted('EDIT');
        $canView = $this->admin->isGranted('LIST');
        if (false === $canView && false === $canEdit) {
            throw new AccessDeniedException();
        }

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $languages = $em->createQuery(
            'SELECT lang FROM ObjectBGTranslationBundle:Language lang INDEX BY lang.id'
        )->getResult();

        $qb = $em->createQueryBuilder()
            ->select('token', 'translation')
            ->from('ObjectBGTranslationBundle:TranslationToken', 'token', 'token.id')
            ->leftJoin('token.translations', 'translation');

        $filterFormBuilder = $this->createFormBuilder(
            null,
            array(
                'translation_domain' => 'ObjectBGTranslationBundle',
            )
        )
            ->setMethod('GET');

        $filterFormBuilder->add(
            'show-only-untranslated',
            'checkbox',
            array(
                'required' => false,
                'label' => 'Show only untranslated',
            )
        );

        $filterFormBuilder->add(
            'filter',
            'submit',
            array(
                'attr' => array(
                    'class' => 'btn btn-primary',
                ),
            )
        );

        $filterForm = $filterFormBuilder->getForm();
        $filterForm->handleRequest($request);
        if ($filterForm->isValid()) {
            if ($filterForm['show-only-untranslated']->getData()) {
                $qb->andWhere('SIZE(token.translations) < :languagesCount')
                    ->setParameter('languagesCount', count($languages));
            }
        }
        $tokens = $qb->getQuery()->getResult();

        $formBuilder = $this->createFormBuilder();
//        $FormBuilder->add('tokens', 'collection', array(
//            'type' => 'text',
//            'label' => false,
//            'allow_add' => true,
//            'options' => array(
//                'label' => false
//            )
//        ));

        $formBuilder->add(
            'translations',
            'collection',
            array(
                'type' => 'collection',
                'disabled' => !$canEdit,
                'label' => false,
                'allow_add' => true,
                'options' => array(
                    'type' => 'text',
                    'label' => false,
                    'required' => false,
                    'options' => array(
                        'label' => false,
                    ),
                ),
            )
        );

        $formBuilder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($tokens, $languages) {
                $data = $event->getData();
                $data['translations'] = array();

                foreach ($tokens as $token) {
                    $data['translations'][$token->getId()] = array();
                    foreach ($languages as $lang) {
                        $data['translations'][$token->getId()][$lang->getId()] = (string)$token->getTranslation($lang);
                    }
                }
                $event->setData($data);
            }
        );

        if ($canEdit) {
            $formBuilder->add(
                'Save',
                'submit',
                array(
                    'attr' => array(
                        'class' => 'btn btn-primary',
                    ),
                )
            );
        }

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $translationsEntities = $em->createQuery(
                'SELECT translation FROM ObjectBGTranslationBundle:Translation translation INDEX BY translation.id'
            )
                ->getResult();
            $translationsEntities = new \Doctrine\Common\Collections\ArrayCollection($translationsEntities);

            foreach ($form['translations'] as $TokenId => $translations) {
                $token = $tokens[$TokenId];
//                $token->setToken($Form['tokens'][$TokenId]->getData());
//                $em->persist($token);

                foreach ($translations as $languageId => $formData) {
                    $language = $languages[$languageId];
                    $translation = $translationsEntities->filter(
                        function ($item) use ($token, $language) {
                            return $item->getTranslationToken() == $token && $item->getLanguage() == $language;
                        }
                    )->first();

                    if ($formData->getData() == null) {
                        if ($translation) {
                            $em->remove($translation);
                        }
                        continue;
                    }
                    if (!$translation) {
                        $translation = new \ObjectBG\TranslationBundle\Entity\Translation();
                        $translation->setLanguage($language);
                        $translation->setTranslationToken($token);
                    }
                    $translation->setTranslation($formData->getData());
                    $em->persist($translation);
                }
            }

            $em->flush();
        }

        $formView = $form->createView();

        $Twig = $this->get('twig');
        $FormExtension = $Twig->getExtension('form');
        $FormExtension->renderer->setTheme($formView, $this->admin->getFormTheme());

        $filterFormView = $filterForm->createView();
        $FormExtension->renderer->setTheme($filterFormView, $this->admin->getFilterTheme());

        return $this->render(
            'ObjectBGTranslationBundle:CRUD:list.html.twig',
            array(
                'action' => 'list',
                'languages' => $languages,
                'tokens' => $tokens,
                'form' => $formView,
                'filterForm' => $filterFormView,
            )
        );
    }
}
