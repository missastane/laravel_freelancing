<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\FavoriteProjectResource;
use App\Http\Resources\User\FavoriteProposalResource;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
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
    public function getUserFavorites()
    {
        $user = auth()->user();

        $myFavorites = $this->model->where('user_id', $user->id);

        if ($user->active_role === 'employer') {
            $result = $myFavorites->whereMorphedTo('favoritable', Proposal::class)
                ->with('favoritable')->paginate(15);

            return new BaseCollection($result, FavoriteProposalResource::class, null);

        } elseif ($user->active_role === 'freelancer') {
            $result = $myFavorites->whereMorphedTo('favoritable', Project::class)
                ->with('favoritable')->paginate(15);

            return new BaseCollection($result, FavoriteProjectResource::class, null);

        }

        return collect([]); // اگه نقش نامعتبر بود
    }


    public function getAuthUserFavoritable(Model $model)
    {
        $favorites = $this->model->whereMorphedTo('favoritable', $model)
            ->where('user_id', auth()->id())
            ->get();
        return $favorites;
    }

    public function firstOrCreate(array $attributes)
    {
        return $this->model->firstOrCreate($attributes);
    }



}