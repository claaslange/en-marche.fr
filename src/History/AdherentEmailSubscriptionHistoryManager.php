<?php

namespace AppBundle\History;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentEmailSubscriptionHistory;
use AppBundle\Repository\AdherentEmailSubscriptionHistoryRepository;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;

class AdherentEmailSubscriptionHistoryManager
{
    private $manager;
    private $adherentRepository;
    private $historyRepository;

    public function __construct(
        AdherentRepository $adherentRepository,
        AdherentEmailSubscriptionHistoryRepository $historyRepository,
        ObjectManager $manager
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->historyRepository = $historyRepository;
        $this->manager = $manager;
    }

    public function createOrUpdateHistory(Adherent $adherent): void
    {
        $histories = $this->historyRepository->findByAdherent($adherent);

        if (0 == count($histories)) {
            $this->createSubscriptionHistory($adherent, $adherent->getEmailsSubscriptions(), $adherent->getReferentTags());
        } else {
            $subscriptions = $adherent->getEmailsSubscriptions();
            foreach ($histories as $history) {
                if (!in_array($history->getSubscribedEmailsType(), $subscriptions)) {
                    $history->setUnsubscribedAt(new \DateTime());
                } else {
                    if (false !== ($key = array_search($history->getSubscribedEmailsType(), $subscriptions))) {
                        unset($subscriptions[$key]);
                    }
                }
            }

            $this->createSubscriptionHistory($adherent, $subscriptions, $adherent->getReferentTags());
        }

        $this->manager->flush();
    }

    public function updateHistoryForZipCodeChanging(Adherent $adherent): void
    {
        $histories = $this->historyRepository->findByAdherent($adherent);

        if (0 == count($histories)) {
            $this->createSubscriptionHistory($adherent, $adherent->getEmailsSubscriptions(), $adherent->getReferentTags());
        } else {
            $tags = $adherent->getReferentTags();
            foreach ($histories as $history) {
                if (!$tags->contains($history->getReferentTag())) {
                    $history->setUnsubscribedAt(new \DateTime());
                } else {
                    $tags->removeElement($history->getReferentTag());
                }
            }

            $this->createSubscriptionHistory($adherent, $adherent->getEmailsSubscriptions(), $tags);
        }

        $this->manager->flush();
    }

    public function createSubscriptionHistory(Adherent $adherent, array $subscriptions, Collection $referentTags): void
    {
        foreach ($subscriptions as $subscription) {
            foreach ($referentTags as $tag) {
                $history = new AdherentEmailSubscriptionHistory($adherent, $subscription, $tag, new \DateTime());
                $this->manager->persist($history);
            }
        }
    }
}
