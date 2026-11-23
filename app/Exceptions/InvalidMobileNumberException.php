<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;

class InvalidMobileNumberException extends Exception
{
    use ApiResponseTrait;

    public function render($request)
    {
        return $this->error(
            __('errors.invalid_mobile_number'),
            422,
            'INVALID_MOBILE_NUMBER'
        );
    }
}