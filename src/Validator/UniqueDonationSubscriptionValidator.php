<?php

namespace AppBundle\Validator;

use AppBundle\Donation\DonationRequest;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Repository\DonationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
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

    public function __construct(DonationRepository $donationRepository, PropertyAccessor $propertyAccessor, UrlGeneratorInterface $urlGenerator)
    {
        $this->donationRepository = $donationRepository;
        $this->propertyAccessor = $propertyAccessor;
        $this->urlGenerator = $urlGenerator;
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

        if ($value->hasSubscription() &&
            $donations = $this->donationRepository->findBy($criteria)
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters([
                    '{{ profile_url }}' => $this->urlGenerator->generate('app_user_profile'),
                    '{{ donation_url }}' => $this->urlGenerator->generate(
                        'donation_informations',
                        ['montant' => $value->getAmount()]
                    ),
                ])
                ->addViolation()
            ;
        }
    }
}
