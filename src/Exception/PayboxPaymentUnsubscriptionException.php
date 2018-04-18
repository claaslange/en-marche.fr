<?php

namespace AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PayboxPaymentUnsubscriptionException extends BadRequestHttpException
{
    public const MAP_ERRORS = [
        1 => 'Incident technique (Configuration)',
        2 => 'Données non cohérentes',
        3 => 'Incident technique (Accès à la base de données)',
        4 => 'Site inconnu',
        9 => 'Echec de la résiliation. Aucun abonnement résilié',
    ];

    private $codeError;

    public function __construct(int $codeError, \Exception $previous = null, $code = 0)
    {
        $this->codeError = $codeError;

        $errorMessage = 'Error unknown';

        if (array_key_exists($codeError, static::MAP_ERRORS)) {
            $errorMessage = static::MAP_ERRORS[$codeError];
        }

        parent::__construct(sprintf('%d: %s', $codeError, $errorMessage), $previous, $code);
    }

    public function getCodeError(): int
    {
        return $this->codeError;
    }
}
