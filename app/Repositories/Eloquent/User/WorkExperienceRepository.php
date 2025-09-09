<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\WorkExperienceResource;
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
    public function getUserExperiences()
    {
        $workExperiences = $this->model->where('user_id', auth()->id())
            ->with('province')
            ->orderBy('created_at')->paginate(15);
        return new BaseCollection($workExperiences, WorkExperienceResource::class, null);
    }

    public function showExperience(WorkExperience $workExperience)
    {
        $result = $this->showWithRelations($workExperience, ['province']);
        return new WorkExperienceResource($result);
    }

}