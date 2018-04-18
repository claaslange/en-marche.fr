<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use Ramsey\Uuid\Uuid;

class PayboxPaymentUnsubscriptionConfirmationMessage extends Message
{
    public static function create(Adherent $adherent, Donation $donation): self
    {
        return new self(
            Uuid::uuid4(),
            '292292', // TODO: put the good id template
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre don mensuel a bien été annulé.',
            [
                'full_name' => self::escape($adherent->getFullName()),
                'donation_uuid' => $donation->getUuid()->toString(),
                'donation_id' => $donation->getId(),
                'created_at' => $donation->getCreatedAt()->format('Y/m/d'),
                'amount' => $donation->getAmount(),
            ]
        );
    }
}
