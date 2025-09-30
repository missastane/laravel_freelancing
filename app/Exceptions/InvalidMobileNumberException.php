<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;

class InvalidMobileNumberException extends Exception
{
    use ApiResponseTrait;

    public function render($request)
    {
        return $this->error('شماره موبایل صحیح نیست یا از قبل وجود دارد',422);
    }
}
