<?php

namespace App\Http\Services\Favorite;

use App\Models\User\Favorite;
use App\Models\User\User;
use App\Repositories\Contracts\User\FavoriteRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;

class FavoriteService
{
    use ApiResponseTrait;

    public function __construct(protected FavoriteRepositoryInterface $favoriteRepository)
    {
    }

    public function getUserFavorites(): Paginator
    {
        return $this->favoriteRepository->getUserFavorites();
    }

    public function addToFavorite(array $data): Favorite
    {
        $user = auth()->user();
        $data['user_id'] = $user->id;
        return $this->favoriteRepository->create($data);
    }
}