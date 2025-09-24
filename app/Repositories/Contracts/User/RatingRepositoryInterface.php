<?php

namespace App\Repositories\Contracts\User;

use App\Repositories\Contracts\CreatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface RatingRepositoryInterface extends CreatableRepositoryInterface
{
    public function getContextRates(string $context, int $contextId);
    public function isAlreadyRated(string $context, int $contextId, ?int $orderId = null):bool;
}