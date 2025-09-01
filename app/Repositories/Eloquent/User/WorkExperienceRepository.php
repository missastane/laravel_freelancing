<?php

namespace App\Repositories\Eloquent\User;

use App\Models\Market\WorkExperience;
use App\Repositories\Contracts\User\WorkExperienceRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class WorkExperienceRepository extends BaseRepository implements WorkExperienceRepositoryInterface
{
    use HasCreateTrait;
    use HasDeleteTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    public function __construct(WorkExperience $model)
    {
        parent::__construct($model);
    }
    public function getUserExperiences(): Paginator
    {
        $workExperiences = $this->model->where('user_id', auth()->id())
            ->orderBy('created_at')->simplePaginate(15);
        return $workExperiences;
    }

    public function showExperience(WorkExperience $workExperience)
    {
        return $this->showWithRelations($workExperience,['freelancer:id,first_name,last_name']);
    }

}