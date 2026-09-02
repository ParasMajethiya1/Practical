<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $message = "Merchant wallet does not have sufficient balance for this payout.";
}
