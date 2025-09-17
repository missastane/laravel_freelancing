<?php

namespace App\Http\Services\Favorite;

use App\Exceptions\FavoriteNotExistException;
use App\Models\User\Favorite;
use App\Models\User\User;
use App\Repositories\Contracts\User\FavoriteRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;

class FavoriteService
{
    use ApiResponseTrait;

    public function __construct(protected FavoriteRepositoryInterface $favoriteRepository)
    {
    }

    public function getUserFavorites()
    {
        return $this->favoriteRepository->getUserFavorites();
    }

    public function addToFavorite(array $data): Favorite
    {
        $user = auth()->user();
        $data['user_id'] = $user->id;
        $attributes = [
            'user_id' => $user->id,
            'favoritable_type' => $data['favoritable_type'],
            'favoritable_id' => $data['favoritable_id'],
        ];
        return $this->favoriteRepository->firstOrCreate($attributes);
    }

    public function removeFavorite(Model $model)
    {
        $favorite = $this->favoriteRepository->getAuthUserFavoritable($model);
        if (!$favorite) {
            throw new FavoriteNotExistException();
        }
        $this->favoriteRepository->delete($favorite);
    }
}