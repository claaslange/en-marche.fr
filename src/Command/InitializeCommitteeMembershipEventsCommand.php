<?php

namespace AppBundle\Command;

use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use AppBundle\Entity\Reporting\CommitteeMembershipEvent;
use AppBundle\Referent\ManagedAreaUtils;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\ReferentTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitializeCommitteeMembershipEventsCommand extends Command
{
    protected static $defaultName = 'app:initialize-committee-membership-events';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $committeeRepository;
    private $referentTagRepository;
    private $em;

    const BATCH_SIZE = 50;

    public function __construct(CommitteeRepository $committeeRepository, ReferentTagRepository $referentTagRepository, EntityManagerInterface $em)
    {
        parent::__construct();

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
        if ($this->isAlreadyInitialize()) {
            $this->io->error('Cannot initialize committee membership events. It already exists.');

            return 1;
        }

        /** @var CommitteeMembership $membership */
        $memberships = $this->findAllCommitteeMembership();

        $this->io->progressStart($this->countCommitteeMembership());

        foreach ($memberships as $i => $membership) {
            $membership = $membership[0];
            $this->io->progressAdvance();
            $committee = $this->committeeRepository->findOneByUuid($membership->getCommitteeUuid()->toString());

            if (!$committee) {
                continue;
            }

            $event = new CommitteeMembershipEvent(
                $membership,
                $committee,
                $this->referentTagRepository->findOneByCode(ManagedAreaUtils::getCodeFromCommittee($committee)),
                CommitteeMembershipAction::JOIN(),
                $membership->getSubscriptionDate()
            );

            $this->em->persist($event);

            if (0 === ($i % self::BATCH_SIZE)) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();
        $this->io->progressFinish();

        $this->io->success('Committee membership events created successfully');
    }

    private function isAlreadyInitialize(): bool
    {
        return 0 < $this->em->createQueryBuilder()
            ->select('count(event)')
            ->from(CommitteeMembershipEvent::class, 'event')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    protected function findAllCommitteeMembership(): \Doctrine\ORM\Internal\Hydration\IterableResult
    {
        return $this->em->createQueryBuilder()
            ->select('membership')
            ->from(CommitteeMembership::class, 'membership')
            ->getQuery()
            ->iterate()
        ;
    }

    private function countCommitteeMembership(): int
    {
        return $this->em->createQueryBuilder()
            ->select('count(membership)')
            ->from(CommitteeMembership::class, 'membership')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
