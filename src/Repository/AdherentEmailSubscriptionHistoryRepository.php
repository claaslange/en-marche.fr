<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentEmailSubscriptionHistory;
use AppBundle\Entity\ReferentTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class AdherentEmailSubscriptionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentEmailSubscriptionHistory::class);
    }

    /**
     * @param Adherent $adherent
     *
     * @return AdherentEmailSubscriptionHistory[]
     */
    public function findByAdherent(Adherent $adherent, $withoutInactives = true): array
    {
        $qb = $this
            ->createQueryBuilder('h')
            ->where('h.adherent = :adherent')
            ->setParameter('adherent', $adherent)
        ;

        if ($withoutInactives) {
            $qb->andWhere('h.unsubscribedAt IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Adherent $adherent
     *
     * @return AdherentEmailSubscriptionHistory[]
     */
    public function findAllByAdherentAndType(Adherent $adherent, string $subscriptionType): array
    {
        return $this
            ->createQueryBuilder('h')
            ->where('h.adherent = :adherent')
            ->andWhere('h.subscribedEmailsType = :type')
            ->orderBy('h.subscribedAt', 'DESC')
            ->setParameter('adherent', $adherent)
            ->setParameter('type', $subscriptionType)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Adherent $adherent
     *
     * @return AdherentEmailSubscriptionHistory[]
     */
    public function findAllByAdherentAndReferentTag(Adherent $adherent, ReferentTag $tag): array
    {
        return $this
            ->createQueryBuilder('h')
            ->where('h.adherent = :adherent')
            ->andWhere('h.referentTag = :tag')
            ->setParameter('adherent', $adherent)
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult()
        ;
    }
}
