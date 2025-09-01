<?php

namespace App\Repositories\Contracts\User;

use App\Repositories\Contracts\CreatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface FavoriteRepositoryInterface extends CreatableRepositoryInterface
{
    public function getUserFavorites(): Paginator;
}