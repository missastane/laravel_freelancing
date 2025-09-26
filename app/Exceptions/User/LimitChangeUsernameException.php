<?php

namespace App\Exceptions\User;

use App\Traits\ApiResponseTrait;
use Exception;

class LimitChangeUsernameException extends Exception
{
    use ApiResponseTrait;

    public function render($request)
    {
        return $this->error('تغییر نام کاربری بیش از ۲ بار مجاز نیست.',403); 
    }
}
