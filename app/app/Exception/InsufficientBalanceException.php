<?php

namespace App\Exception;

class InsufficientBalanceException extends \DomainException {
    public function __construct(string $message = 'Saldo insuficiente', $code = 404)
    {
        parent::__construct($message, $code);
    }
}
