<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User\Favorite;
use App\Repositories\Contracts\User\FavoriteRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class FavoriteRepository extends BaseRepository implements FavoriteRepositoryInterface
{
    use HasCreateTrait;
    public function __construct(Favorite $model)
    {
        parent::__construct($model);
    }
    public function getUserFavorites(): Paginator
    {
        $user = auth()->user();
        $myFavorites = $this->model->where('user_id', $user->id)
            ->simplePaginate(20)->appends(['favoritable']);
        return $myFavorites;
    }

}