<?php

namespace App\Enum;

enum WithdrawStatus: string 
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case PROCESSING = 'processing';
    case ERROR = 'error';
}
