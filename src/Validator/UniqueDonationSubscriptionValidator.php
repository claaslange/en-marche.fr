<?php

namespace AppBundle\Validator;

use AppBundle\Donation\DonationRequest;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Repository\DonationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueDonationSubscriptionValidator extends ConstraintValidator
{
    private $donationRepository;
    private $propertyAccessor;
    private $urlGenerator;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(DonationRepository $donationRepository, PropertyAccessor $propertyAccessor, UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->donationRepository = $donationRepository;
        $this->propertyAccessor = $propertyAccessor;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    /**
     * @param DonationRequest $value
     * @param UniqueEntity    $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, UniqueCommittee::class);
        }

        if (!$value instanceof DonationRequest) {
            throw new UnexpectedTypeException($value, DonationRequest::class);
        }

        if (PayboxPaymentSubscription::NONE === $value->getDuration()) {
            return;
        }

        $criteria = [];
        foreach ($constraint->fields as $field) {
            $criteria[$field] = $this->propertyAccessor->getValue($value, $field);
        }

        if ($donations = $this->donationRepository->findSubscriptions($criteria)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters([
                    '{{ profile_url }}' => $this->urlGenerator->generate('app_user_profile'),
                    '{{ donation_url }}' => $this->urlGenerator->generate(
                        'donation_informations',
                        ['montant' => $this->requestStack->getCurrentRequest()->get('montant')]
                    ),
                ])
                ->addViolation()
            ;
        }
    }
}
