<?php
// src/Exception/InvalidPaymentSessionException.php

namespace App\Exception;

use RuntimeException;

class InvalidPaymentSessionException extends RuntimeException
{
    public function __construct(string $message = "Session de paiement invalide", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
