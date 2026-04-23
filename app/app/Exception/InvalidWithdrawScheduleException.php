<?php

namespace App\Exception;

class InvalidWithdrawScheduleException extends \DomainException {
    public function __construct(string $message = 'Data de agendamento inválida', $code = 400)
    {
        parent::__construct($message, $code);
    }
}
