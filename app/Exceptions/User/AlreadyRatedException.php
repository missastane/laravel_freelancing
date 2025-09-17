<?php

namespace App\Exceptions\User;

use App\Traits\ApiResponseTrait;
use Exception;

class AlreadyRatedException extends Exception
{
    use ApiResponseTrait;

    public function render($request)
    {
        return $this->error('شما قبلا به این کاربر امتیاز داده اید', 409);
    }
}
