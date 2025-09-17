<?php

namespace App\Repositories\Contracts\User;

use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;

interface FavoriteRepositoryInterface extends DeletableRepositoryInterface
{
    public function getUserFavorites();
    public function getAuthUserFavoritable(Model $model);
    public function firstOrCreate(array $attributes);
}