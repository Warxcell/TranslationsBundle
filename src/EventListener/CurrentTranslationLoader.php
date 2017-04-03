<?php

namespace ObjectBG\TranslationBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use ObjectBG\TranslationBundle\TranslatableInterface;
use Symfony\Component\DependencyInjection\Container;

class CurrentTranslationLoader implements EventSubscriber
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array('postLoad');
    }

    public function postLoad($event)
    {
        $entity = $event->getEntity();
        if (!$entity instanceof TranslatableInterface) {
            return;
        }

        $loader = $this->container->get('object_bg.translation.current_translation_loader');
        $loader->initializeCurrentTranslation($entity);
    }
}
