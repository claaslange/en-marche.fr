<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Donation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class DonationRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donation::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(string $uuid): ?Donation
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function findByEmailAddressOrderedByDonatedAt(string $email, string $order = 'ASC'): array
    {
        return $this->findBy(
            ['emailAddress' => $email],
            ['donatedAt' => $order]
        );
    }
}
