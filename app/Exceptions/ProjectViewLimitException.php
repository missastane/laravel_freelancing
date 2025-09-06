<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;

class ProjectViewLimitException extends Exception
{
    use ApiResponseTrait;
    public function render($request)
    {
        return $this->error('شما به حداکثر تعداد مجاز مشاهده جزئیات پروژه‌ها رسیده‌اید.',429);
    }
}
