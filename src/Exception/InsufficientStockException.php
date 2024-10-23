<?php
// src/Exception/InsufficientStockException.php

namespace App\Exception;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(string $message = "Stock insuffisant", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
