<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentEmailSubscriptionHistory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAdherentEmailSubscriptionHistoryData extends AbstractFixture implements DependentFixtureInterface
{
    private $adherentRepository;

    public function load(ObjectManager $manager)
    {
        $this->adherentRepository = $manager->getRepository(Adherent::class);
        $adherents = $this->adherentRepository->findAll();

        foreach ($adherents as $key => $adherent) {
            foreach ($adherent->getEmailsSubscriptions() as $subscription) {
                foreach ($adherent->getReferentTags() as $tag) {
                    $history = new AdherentEmailSubscriptionHistory($adherent, $subscription, $tag, new \DateTime(sprintf('-%s0 days', $key + 1)));
                    if (in_array($adherent->getUuid(), [LoadAdherentData::ADHERENT_2_UUID, LoadAdherentData::ADHERENT_4_UUID])) {
                        $history->setUnsubscribedAt(new \DateTime(sprintf('-%s0 days', $key)));
                    }

                    $manager->persist($history);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadReferentTagData::class,
        ];
    }
}
