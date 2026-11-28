<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;

class WrongCurrentPasswordException extends Exception
{
    use ApiResponseTrait;

    public function render($request)
    {
        return $this->error(__('errors.wrong_current_password'),403);
       
    }
}
