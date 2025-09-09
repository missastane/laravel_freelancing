<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\UserEducationResource;
use App\Models\Market\UserEducation;
use App\Repositories\Contracts\User\UserEducationRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class UserEducationRepository extends BaseRepository implements UserEducationRepositoryInterface
{
    use HasCreateTrait;
    use HasUpdateTrait;
    use HasShowTrait;
    use HasDeleteTrait;
    public function __construct(UserEducation $model)
    {
        parent::__construct($model);
    }
    public function getUserEducations()
    {
        $user = auth()->user();
        $userEducations = $this->model->where('user_id', $user->id)->with('province')
            ->orderBy('created_at')->paginate(15);
        return new BaseCollection($userEducations, UserEducationResource::class,null);
    }

    public function showEducation(UserEducation $userEducation)
    {
        $result = $this->showWithRelations($userEducation, ['province']);
        return new UserEducationResource($result);
    }

}