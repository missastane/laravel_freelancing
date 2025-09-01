<?php

namespace App\Repositories\Eloquent\User;

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
    public function getUserEducations(): Paginator
    {
        $user = auth()->user();
        $userEducations = $this->model->where('user_id', $user->id)
            ->orderBy('created_at')->simplePaginate(15);
        return $userEducations;
    }

    public function showEducation(UserEducation $userEducation)
    {
        return $this->showWithRelations($userEducation, ['freelancer:id,first_name,last_name']);
    }

}