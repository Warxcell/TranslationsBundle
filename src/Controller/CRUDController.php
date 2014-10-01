<?php

namespace ObjectBG\TranslationBundle\Controller;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class CRUDController extends \Sonata\AdminBundle\Controller\CRUDController {

    public function listAction() {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }
        $Request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $languages = $em->createQuery('SELECT lang FROM ObjectBGTranslationBundle:Language lang INDEX BY lang.id')->getResult();

        $qb = $em->createQueryBuilder()
                ->select('token', 'translation')
                ->from('ObjectBGTranslationBundle:TranslationToken', 'token', 'token.id')
                ->join('token.translations', 'translation')
        ;


        $FilterFormBuilder = $this->createFormBuilder(null, array(
                    'translation_domain' => 'ObjectBGTranslationBundle'
                ))
                ->setMethod('GET');

        $FilterFormBuilder->add('show-only-untranslated', 'checkbox', array(
            'required' => false,
            'label' => 'Show only untranslated'
        ));

        $FilterFormBuilder->add('filter', 'submit', array(
            'attr' => array(
                'class' => 'btn btn-primary'
            )
        ));

        $FilterForm = $FilterFormBuilder->getForm();
        $FilterForm->handleRequest($Request);
        if ($FilterForm->isValid()) {
            if ($FilterForm['show-only-untranslated']->getData()) {
                $qb->andWhere('SIZE(token.translations) < :languagesCount')
                        ->setParameter('languagesCount', count($languages));
            }
        }
        $tokens = $qb->getQuery()->getResult();

        $FormBuilder = $this->createFormBuilder();
//        $FormBuilder->add('tokens', 'collection', array(
//            'type' => 'text',
//            'label' => false,
//            'allow_add' => true,
//            'options' => array(
//                'label' => false
//            )
//        ));

        $FormBuilder->add('translations', 'collection', array(
            'type' => 'collection',
            'label' => false,
            'allow_add' => true,
            'options' => array(
                'type' => 'text',
                'label' => false,
                'required' => false,
                'options' => array(
                    'label' => false
                )
            )
        ));

        $FormBuilder->addEventListener(
                FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($tokens, $languages) {
            $data = $event->getData();
//            $data['tokens'] = array();
            $data['translations'] = array();

//            foreach ($tokens as $token) {
//                $data['tokens'][$token->getId()] = $token->getToken();
//            }
            foreach ($tokens as $token) {
                $data['translations'][$token->getId()] = array();
                foreach ($languages as $lang) {
                    $data['translations'][$token->getId()][$lang->getId()] = (string) $token->getTranslation($lang);
                }
            }
            $event->setData($data);
        });

        $FormBuilder->add('Save', 'submit', array(
            'attr' => array(
                'class' => 'btn btn-primary'
            )
        ));

        $Form = $FormBuilder->getForm();
        $Form->handleRequest($Request);
        if ($Form->isValid()) {
            $TranslationsEntities = $em->createQuery('SELECT translation FROM ObjectBGTranslationBundle:Translation translation INDEX BY translation.id')
                    ->getResult();
            $TranslationsEntities = new \Doctrine\Common\Collections\ArrayCollection($TranslationsEntities);

            foreach ($Form['translations'] as $TokenId => $Translations) {
                $token = $tokens[$TokenId];
//                $token->setToken($Form['tokens'][$TokenId]->getData());
//                $em->persist($token);

                foreach ($Translations as $LanguageId => $FormData) {
                    $language = $languages[$LanguageId];
                    $Translation = $TranslationsEntities->filter(function($item) use ($token, $language) {
                                return $item->getTranslationToken() == $token && $item->getLanguage() == $language;
                            })->first();

                    if ($FormData->getData() == null) {
                        if ($Translation) {
                            $em->remove($Translation);
                        }
                        continue;
                    }
                    if (!$Translation) {
                        $Translation = new \ObjectBG\TranslationBundle\Entity\Translation();
                        $Translation->setLanguage($language);
                        $Translation->setTranslationToken($token);
                        $Translation->setCatalogue('messages');
                    }
                    $Translation->setTranslation($FormData->getData());
                    $em->persist($Translation);
                }
            }

            $em->flush();
            $this->get('object_bg.translation.helper')->clearTranslationCache();
        }

        $FormView = $Form->createView();

        $Twig = $this->get('twig');
        $FormExtension = $Twig->getExtension('form');
        $FormExtension->renderer->setTheme($FormView, $this->admin->getFormTheme());

        $FilterFormView = $FilterForm->createView();
        $FormExtension->renderer->setTheme($FilterFormView, $this->admin->getFilterTheme());

        return $this->render('ObjectBGTranslationBundle:CRUD:list.html.twig', array(
                    'action' => 'list',
                    'languages' => $languages,
                    'tokens' => $tokens,
                    'form' => $FormView,
                    'filterForm' => $FilterFormView
        ));
    }

}
