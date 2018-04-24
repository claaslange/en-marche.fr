<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\ReferentTag;
use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use AppBundle\Entity\Reporting\CommitteeMembershipEvent;
use AppBundle\Referent\ManagedAreaUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCommitteeMembershipEventData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $committeeRepository = $manager->getRepository(Committee::class);
        $referentTagRepository = $manager->getRepository(ReferentTag::class);
        $memberships = $manager->getRepository(CommitteeMembership::class)->findAll();

        foreach ($memberships as $membership) {
            $event = new CommitteeMembershipEvent(
                $membership,
                $committee = $committeeRepository->findOneByUuid($membership->getCommitteeUuid()->toString()),
                $referentTagRepository->findOneByCode(ManagedAreaUtils::getCodeFromCommittee($committee)),
                CommitteeMembershipAction::JOIN(),
                $membership->getSubscriptionDate()
            );

            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [LoadAdherentData::class];
    }
}
