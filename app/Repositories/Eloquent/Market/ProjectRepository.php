<?php

namespace App\Repositories\Eloquent\Market;

use App\Http\Resources\Market\ProjectResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Market\Project;
use App\Models\User\User;
use App\Repositories\Contracts\Market\ProjectRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    protected function formatProjects($projects)
    {
        /** @var \Illuminate\Pagination\AbstractPaginator $projects */
        $projects->getCollection()->each(function ($project) {
            $project->makeHidden('status')->append('status_value');
        });
    }
    public function getProjects(array $data)
    {
        $projects = $this->model->filter($data)->with('employer','category','files','skills')->orderBy('title')->paginate(15);
        return new BaseCollection($projects, ProjectResource::class, null);
    }
    public function searchProject(string $search)
    {
        $projects = $this->model->where('title', 'LIKE', "%$search%")->orderBy('title')->paginate(15);
        return new BaseCollection($projects, ProjectResource::class, null);
    }
    public function getUserProjects(?User $user, array $data)
    {
        $user = $user ?? auth()->user();
        $projects = $this->model->filter($data)->where('user_id',$user->id)->with('proposals')
            ->orderBy('created_at', 'desc')->paginate(15);
        return new BaseCollection($projects, ProjectResource::class, null);
    }
    public function showProject(Project $project)
    {
        if (auth()->user()->active_role !== 'employer') {
            $project = $this->showWithRelations($project, ['files','skills']);
        }
        $project = $this->showWithRelations($project, ['proposals', 'files']);
        return new ProjectResource($project);
    }

    public function syncSkills(Project $project, array $skills)
    {
        return $project->skills()->sync($skills);
    }

}