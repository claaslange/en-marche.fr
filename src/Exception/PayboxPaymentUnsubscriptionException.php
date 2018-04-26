<?php

namespace AppBundle\Exception;

use AppBundle\Donation\PayboxPaymentUnsubscriptionErrorEnum;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PayboxPaymentUnsubscriptionException extends BadRequestHttpException
{
    private $codeError;

    public function __construct(int $codeError, \Exception $previous = null, $code = 0)
    {
        $this->codeError = $codeError;

        $errorMessage = 'Error unknown';

        if (PayboxPaymentUnsubscriptionErrorEnum::isValidKey($key = "ERROR_$codeError")) {
            $errorMessage = PayboxPaymentUnsubscriptionErrorEnum::$key();
        }

        parent::__construct(sprintf('%d: %s', $codeError, $errorMessage), $previous, $code);
    }

    public function getCodeError(): int
    {
        return $this->codeError;
    }
}
