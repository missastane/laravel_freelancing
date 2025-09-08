<?php

namespace App\Exceptions\Market;

use App\Traits\ApiResponseTrait;
use Exception;

class NotEnoughBalanceException extends Exception
{
    use ApiResponseTrait;
    public function render($request)
    {
        return $this->error("موجودی کیف پول شما برای انجام این عملیات کافی نیست",403);
    }
}
