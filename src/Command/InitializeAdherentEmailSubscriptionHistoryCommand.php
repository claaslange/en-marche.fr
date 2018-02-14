<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentEmailSubscriptionHistory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitializeAdherentEmailSubscriptionHistoryCommand extends ContainerAwareCommand
{
    private const COMMAND_NAME_ALIAS = 'app:adherent:iesh';
    private const BATCH_SIZE = 50;

    protected static $defaultName = 'app:adherent:initialize-email-subscriptions-history';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EntityManager
     */
    private $em;

    protected function configure()
    {
        $this
            ->setAliases([self::COMMAND_NAME_ALIAS])
            ->setDescription('Create email subscriptions history. The history will be created for both users and adherents even inactives.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->isAlreadyInitialize()) {
            $this->io->error('Impossible to initialize email subscription history. It already exists.');

            return 1;
        }

        $this->io->title('Starting email subscription history initialization.');

        $progressBar = new ProgressBar($output, $this->getAdherentCount());

        $this->em->beginTransaction();

        $nb = 0;
        foreach ($this->getAdherents() as $result) {
            $adherent = reset($result);

            foreach ($adherent->getReferentTags() as $referentTag) {
                foreach ($adherent->getEmailsSubscriptions() as $subscription) {
                    $subscriptionHistory = new AdherentEmailSubscriptionHistory($adherent, $subscription, $referentTag, $adherent->activatedAt ?? $adherent->registeredAt);
                    $this->em->persist($subscriptionHistory);
                }
            }

            $progressBar->advance();

            ++$nb;

            if (0 === ($nb % self::BATCH_SIZE)) {
                $this->em->flush();
                $this->em->clear();
                $nb = 0;
            }
        }

        $this->em->flush();
        $this->em->commit();

        $progressBar->finish();

        $this->io->newLine(2);
        $this->io->success('Email subscription history initialized successfully!');
    }

    private function isAlreadyInitialize(): bool
    {
        $nbHistories = $this
            ->em
            ->getRepository(AdherentEmailSubscriptionHistory::class)
            ->createQueryBuilder('h')
            ->select('COUNT(h)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $nbHistories > 0;
    }

    private function getAdherents(): IterableResult
    {
        return $this
            ->getQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getAdherentCount(): int
    {
        return $this
            ->getQueryBuilder()
            ->select('COUNT(a)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository(Adherent::class)
            ->createQueryBuilder('a')
        ;
    }
}
