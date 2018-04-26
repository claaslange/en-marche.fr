<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Exception\PayboxPaymentUnsubscriptionException;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\PayboxPaymentUnsubscriptionConfirmationMessage;
use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request;

class PayboxPaymentUnsubscription
{
    private $request;
    private $donationRequestUtils;
    private $mailer;

    public function __construct(MailerService $mailer, Request $request, DonationRequestUtils $donationRequestUtils)
    {
        $this->mailer = $mailer;
        $this->request = $request;
        $this->donationRequestUtils = $donationRequestUtils;
    }

    /**
     * @throws PayboxPaymentUnsubscriptionException
     */
    public function unsubscribe(Donation $donation): void
    {
        $result = [];
        parse_str($this->request->cancel($this->donationRequestUtils->buildDonationReference($donation)), $result);

        if ('OK' !== $result['ACQ']) {
            throw new PayboxPaymentUnsubscriptionException($result['ERREUR']);
        }

        $donation->subscriptionEnded();
    }

    public function sendConfirmationMessage(Donation $donation, Adherent $adherent): void
    {
        $this->mailer->sendMessage(PayboxPaymentUnsubscriptionConfirmationMessage::create($adherent, $donation));
    }
}
