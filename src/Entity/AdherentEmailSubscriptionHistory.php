<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="adherent_email_subscription_history")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdherentEmailSubscriptionHistoryRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class AdherentEmailSubscriptionHistory
{
    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @ORM\Column(length=50)
     */
    private $subscribedEmailsType;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $referentTag;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $subscribedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $unsubscribedAt;

    public function __construct(
        Adherent $adherent,
        string $subscribedEmailsType,
        ReferentTag $referentTag,
        \DateTime $subscribedAt,
        \DateTime $unsubscribedAt = null
    ) {
        $this->adherent = $adherent;
        $this->subscribedEmailsType = $subscribedEmailsType;
        $this->referentTag = $referentTag;
        $this->subscribedAt = $subscribedAt;
        $this->unsubscribedAt = $unsubscribedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getSubscribedEmailsType(): ?string
    {
        return $this->subscribedEmailsType;
    }

    public function getReferentTag(): ReferentTag
    {
        return $this->referentTag;
    }

    public function getSubscribedAt(): \DateTime
    {
        return $this->subscribedAt;
    }

    public function getUnsubscribedAt(): ?\DateTime
    {
        return $this->unsubscribedAt;
    }

    public function setUnsubscribedAt(\DateTime $unsubscribedAt): void
    {
        $this->unsubscribedAt = $unsubscribedAt;
    }
}
