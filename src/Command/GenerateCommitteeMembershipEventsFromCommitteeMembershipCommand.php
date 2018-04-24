<?php

namespace AppBundle\Command;

use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use AppBundle\Entity\Reporting\CommitteeMembershipEvent;
use AppBundle\Referent\ManagedAreaUtils;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\ReferentTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateCommitteeMembershipEventsFromCommitteeMembershipCommand extends Command
{
    protected static $defaultName = 'app:generate-committee-membership-events-from-committee-membership';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $committeeMembershipRepository;
    private $committeeRepository;
    private $referentTagRepository;
    private $em;

    const BATCH_SIZE = 30;

    public function __construct(CommitteeMembershipRepository $committeeMembershipRepository, CommitteeRepository $committeeRepository, ReferentTagRepository $referentTagRepository, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->em = $em;
        $this->committeeRepository = $committeeRepository;
        $this->referentTagRepository = $referentTagRepository;
    }

    protected function configure()
    {
        $this->setDescription('Generate Committee Membership Events from actual Committee Membership');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CommitteeMembership $membership */
        $memberships = $this->committeeMembershipRepository->findAll();

        $this->io->progressStart(count($memberships));
        foreach ($memberships as $i => $membership) {
            $this->io->progressAdvance();
            $committee = $this->committeeRepository->findOneByUuid($membership->getCommitteeUuid()->toString());

            if ($committee) {
                $tag = $this->referentTagRepository->findOneByCode(ManagedAreaUtils::getCodeFromCommittee($committee));
            }

            $event = new CommitteeMembershipEvent(
                $membership,
                $committee,
                $tag ?? null,
                CommitteeMembershipAction::JOIN(),
                $membership->getSubscriptionDate()
            );

            if ($this->eventAlreadyInDb($event)) {
                $this->io->note("Membership '{$membership->getUuid()->toString()}' already recorded as event");

                continue;
            }

            $this->em->persist($event);

            if (0 === ($i % self::BATCH_SIZE)) {
                $this->em->flush();
                $this->em->clear(CommitteeMembershipEvent::class);
            }
        }

        $this->em->flush();
        $this->em->clear();
        $this->io->progressFinish();

        $this->io->success('Committe membership events created successfuly');
    }

    private function eventAlreadyInDb(CommitteeMembershipEvent $event): bool
    {
        return 0 < $this->em->createQueryBuilder()
            ->select('count(event)')
            ->from(CommitteeMembershipEvent::class, 'event')
            ->where('event.action = :action')
            ->andWhere('event.adherent = :adherent')
            ->andWhere('event.committee = :committee')
            ->andWhere('event.tag = :tag')
            ->andWhere('event.privilege = :privilege')
            ->andWhere('event.date = :date')
            ->setParameter('action', $event->getAction())
            ->setParameter('adherent', $event->getAdherent())
            ->setParameter('committee', $event->getCommittee())
            ->setParameter('tag', $event->getTag())
            ->setParameter('privilege', $event->getPrivilege())
            ->setParameter('date', $event->getDate())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
