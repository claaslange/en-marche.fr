<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request as LexikRequestHandler;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayboxFormFactory
{
    private $environment;
    private $requestHandler;
    private $router;
    private $donationRequestUtils;

    public function __construct(string $environment, LexikRequestHandler $requestHandler, Router $router, DonationRequestUtils $donationRequestUtils)
    {
        $this->environment = $environment;
        $this->requestHandler = $requestHandler;
        $this->router = $router;
        $this->donationRequestUtils = $donationRequestUtils;
    }

    public function createPayboxFormForDonation(Donation $donation): Request
    {
        $callbackParameters = $this->donationRequestUtils->buildCallbackParameters();

        $parameters = [
            'PBX_CMD' => $this->donationRequestUtils->buildDonationReference($donation),
            'PBX_PORTEUR' => $donation->getEmailAddress(),
            'PBX_TOTAL' => $donation->getAmount(),
            'PBX_DEVISE' => '978',
            'PBX_RETOUR' => 'id:R;authorization:A;result:E;transaction:S;amount:M;date:W;time:Q;card_type:C;card_end:D;card_print:H',
            'PBX_TYPEPAIEMENT' => 'CARTE',
            'PBX_TYPECARTE' => 'CB',
            'PBX_RUF1' => 'POST',
            'PBX_EFFECTUE' => $this->router->generate('donation_callback', $callbackParameters, UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_REFUSE' => $this->router->generate('donation_callback', $callbackParameters, UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_ANNULE' => $this->router->generate('donation_callback', $callbackParameters, UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_REPONDRE_A' => $this->router->generate('lexik_paybox_ipn', ['time' => time()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        if (0 === strpos($this->environment, 'test')) {
            $parameters['PBX_REPONDRE_A'] = 'https://httpbin.org/status/200';
        }

        return $this->requestHandler->setParameters($parameters);
    }
}
