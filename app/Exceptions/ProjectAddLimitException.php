<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;

class ProjectAddLimitException extends Exception
{
    use ApiResponseTrait;
    public function render($request)
    {
        return $this->error('شما به حداکثر تعداد مجاز ایجاد پروژه رسیده‌اید',429);
    }
}
