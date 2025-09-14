<?php

namespace App\Exceptions\Market;

use App\Traits\ApiResponseTrait;
use Exception;

class NotAllowedToSetFinalFile extends Exception
{
    use ApiResponseTrait;
    public function render($request)
    {
        return $this->error('عملیات غیرمجاز',403);
    }
}
