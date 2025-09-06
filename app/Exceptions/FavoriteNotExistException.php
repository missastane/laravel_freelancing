<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;

class FavoriteNotExistException extends Exception
{
    use ApiResponseTrait;
    public function render($request)
    {
        return $this->error('این مورد در لیست علاقمندی های شما وجود ندارد', 404);
    }
}
