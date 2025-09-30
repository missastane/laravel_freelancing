<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;

class WrongCurrentPasswordException extends Exception
{
    use ApiResponseTrait;

    public function render($request)
    {
        return $this->error('کلمه عبور فعلی نادرست است',403);
       
    }
}
