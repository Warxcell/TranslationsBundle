<?php

namespace ObjectBG\TranslationBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author David ALLIX
 */
class TranslationsFormsListener implements EventSubscriberInterface
{

    /**
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        $data = $event->getData();

        foreach ($data as $locale => $translation) {
            // Remove useless Translation object
            if (!$translation) {
                $data->removeElement($translation);
            } else {
                $translation->setLocale($locale);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => 'submit',
        );
    }
}
