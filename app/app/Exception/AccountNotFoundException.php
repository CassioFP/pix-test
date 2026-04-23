<?php

namespace App\Exception;

class AccountNotFoundException extends \DomainException {
    public function __construct(string $message = 'Conta não encontrada', $code = 404)
    {
        parent::__construct($message, $code);
    }
}
