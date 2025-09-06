<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User\Favorite;
use App\Repositories\Contracts\User\FavoriteRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;

class FavoriteRepository extends BaseRepository implements FavoriteRepositoryInterface
{
    use HasCreateTrait;
    use HasDeleteTrait;
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

    public function getAuthUserFavoritable(Model $model)
    {
        $favorite = $this->model->whereMorphedTo('favoritable', $model)
            ->where('user_id', auth()->id())
            ->first();
        return $favorite;
    }

    public function firstOrCreate(array $attributes)
    {
        return $this->model->firstOrCreate($attributes);
    }



}