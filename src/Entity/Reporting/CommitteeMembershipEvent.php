<?php

namespace AppBundle\Entity\Reporting;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\ReferentTag;
use AppBundle\Referent\ManagedAreaUtils;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="committees_membership_events")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class CommitteeMembershipEvent
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    protected $uuid;

    /**
     * The committee UUID.
     *
     * @var Committee|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $committee;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @var ReferentTag|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ReferentTag")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tag;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $action;

    /**
     * The privilege given to the member in the committee.
     *
     * Privilege is either HOST or FOLLOWER.
     *
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $privilege;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function __construct(CommitteeMembership $committeeMembership, ?Committee $committee, ReferentTag $tag, CommitteeMembershipAction $action, \DateTimeInterface $date = null)
    {
        if ($committee && !$committee->getUuid()->equals($committeeMembership->getCommitteeUuid())) {
            throw new \InvalidArgumentException('Committee UUIDs mismatch');
        }

        if ($committee && ManagedAreaUtils::getCodeFromCommittee($committee) !== $tag->getCode()) {
            throw new \InvalidArgumentException('Referent tag code mismatch');
        }

        $this->uuid = Uuid::uuid4();
        $this->adherent = $committeeMembership->getAdherent();
        $this->tag = $tag;
        $this->committee = $committee;
        $this->privilege = $committeeMembership->getPrivilege();
        $this->date = $date ?: new \DateTime();
        $this->action = $action->getValue();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getTag(): ReferentTag
    {
        return $this->tag;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getPrivilege(): string
    {
        return $this->privilege;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }
}
