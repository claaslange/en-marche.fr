<?php

namespace AppBundle\Form\EventListener;

use AppBundle\History\AdherentEmailSubscriptionHistoryManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AdherentEmailSubscriptionListener implements EventSubscriberInterface
{
    private $historyManager;
    private $isChanged;

    public function __construct(AdherentEmailSubscriptionHistoryManager $historyManager)
    {
        $this->historyManager = $historyManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        );
    }

    public function onPreSubmit(FormEvent $event)
    {
        $object = $event->getForm()->getData();
        $data = $event->getData();

        $this->isChanged = count(array_diff($data['emails_subscriptions'], $object->getEmailsSubscriptions())) > 0
        || count(array_diff($object->getEmailsSubscriptions(), $data['emails_subscriptions'])) > 0;
    }

    public function onPostSubmit(FormEvent $event)
    {
        if ($this->isChanged) {
            $this->historyManager->createOrUpdateHistory($event->getData());
        }
    }
}
