<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Donation\DonationRequest;
use AppBundle\Donation\DonationRequestUtils;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Donation\PayboxPaymentUnsubscription;
use AppBundle\Entity\Donation;
use AppBundle\Exception\PayboxPaymentUnsubscriptionException;
use AppBundle\Form\DonationRequestType;
use AppBundle\Repository\DonationRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/don")
 */
class DonationController extends Controller
{
    /**
     * @Route(defaults={"_enable_campaign_silence"=true}, name="donation_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->render('donation/index.html.twig', [
                'amount' => DonationRequest::DEFAULT_AMOUNT,
            ]);
        }

        return $this->redirectToRoute('donation_informations', [
            'montant' => $amount,
            'abonnement' => $request->query->has('abonnement') ? // Force unlimited subscription if needed
                PayboxPaymentSubscription::UNLIMITED : PayboxPaymentSubscription::NONE,
        ]);
    }

    /**
     * @Route("/coordonnees", defaults={"_enable_campaign_silence"=true}, name="donation_informations")
     * @Method({"GET", "POST"})
     */
    public function informationsAction(Request $request)
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->redirectToRoute('donation_index');
        }

        $subscription = $request->query->getInt('abonnement', PayboxPaymentSubscription::NONE);

        if (!PayboxPaymentSubscription::isValid($subscription)) {
            return $this->redirectToRoute('donation_index');
        }

        $donationRequest = $this->get(DonationRequestUtils::class)
            ->createFromRequest($request, (float) $amount, $subscription, $this->getUser())
        ;

        $form = $this->createForm(DonationRequestType::class, $donationRequest, ['locale' => $request->getLocale()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.donation_request.handler')->handle($donationRequest);

            return $this->redirectToRoute('donation_pay', [
                'uuid' => $donationRequest->getUuid()->toString(),
            ]);
        }

        return $this->render('donation/informations.html.twig', [
            'form' => $form->createView(),
            'donation' => $donationRequest,
        ]);
    }

    /**
     * @Route(
     *     "/{uuid}/paiement",
     *     requirements={"uuid"="%pattern_uuid%"},
     *     defaults={"_enable_campaign_silence"=true},
     *     name="donation_pay"
     * )
     * @Method("GET")
     */
    public function payboxAction(Donation $donation)
    {
        $paybox = $this->get('app.donation.form_factory')->createPayboxFormForDonation($donation);

        return $this->render('donation/paybox.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/callback/{_callback_token}", defaults={"_enable_campaign_silence"=true}, name="donation_callback")
     * @Method("GET")
     */
    public function callbackAction(Request $request, $_callback_token)
    {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('donation_index');
        }

        return $this->get('app.donation.transaction_callback_handler')->handle($id, $request, $_callback_token);
    }

    /**
     * @Route(
     *     "/{uuid}/{status}",
     *     requirements={"status"="effectue|erreur", "uuid"="%pattern_uuid%"},
     *     defaults={"_enable_campaign_silence"=true},
     *     name="donation_result"
     * )
     * @Method("GET")
     */
    public function resultAction(Request $request, Donation $donation)
    {
        $retryUrl = null;
        if (!$donation->isSuccessful()) {
            $retryUrl = $this->generateUrl(
                'donation_informations',
                $this->get(DonationRequestUtils::class)->createRetryPayload($donation, $request)
            );
        }

        return $this->render('donation/result.html.twig', [
            'successful' => $donation->isSuccessful(),
            'error_code' => $request->query->get('code'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
        ]);
    }

    /**
     * @Route(
     *     "/mensuel/annuler",
     *     defaults={"_enable_campaign_silence"=true},
     *     name="donation_subscription_cancel"
     * )
     * @Method("GET")
     */
    public function cancelSubscriptionAction(DonationRepository $donationRepository, PayboxPaymentUnsubscription $payboxPaymentUnsubscription): RedirectResponse
    {
        $donations = $donationRepository->findAllSubscribedDonationByEmail($this->getUser()->getEmailAddress());

        foreach ($donations as $donation) {
            try {
                $payboxPaymentUnsubscription->unsubscribe($donation);
                $this->getDoctrine()->getManager()->flush();
                $payboxPaymentUnsubscription->sendConfirmationMessage($donation, $this->getUser());
                $this->addFlash(
                    'success',
                    'Votre don mensuel a bien été annulé. Vous recevrez bientôt un mail de confirmation.'
                );
            } catch (PayboxPaymentUnsubscriptionException $payboxPaymentUnsubscriptionException) {
                $this->addFlash(
                    'danger',
                    'La requête n\'a pas abouti, veuillez réessayer s\'il vous plait. Si le problème persiste, merci de nous contacter en <a href="https://contact.en-marche.fr/" target="_blank">cliquant ici</a>'
                );
            }
        }

        if (!$donations) {
            $this->addFlash(
                'danger',
                'Auccun don mensuel n\'a été trouvé'
            );
        }

        return $this->redirect($this->generateUrl('app_user_profile_donation'));
    }
}
