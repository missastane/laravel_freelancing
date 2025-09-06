<?php

namespace App\Repositories\Eloquent\Market;

use App\Http\Resources\Market\ProjectCategoryResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Market\ProjectCategory;
use App\Repositories\Contracts\Market\ProjectCategoryRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Contracts\Pagination\Paginator;

class ProjectCategoryRepository extends BaseRepository implements ProjectCategoryRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(ProjectCategory $model)
    {
        parent::__construct($model);
    }
    public function getCategoies()
    {
        $projectCategories = $this->all(['parent:id,name', 'tags']);
        return new BaseCollection($projectCategories, ProjectCategoryResource::class, null);
    }
    public function searchCategories(string $search)
    {
        $catgories = $this->model->where('name', 'LIKE', "%" . $search . "%")
            ->orWhere('description', 'LIKE', '%' . $search . '%')->with('parent:id,name')
            ->orderBy('name')
            ->paginate(15);
        return new BaseCollection($catgories, ProjectCategoryResource::class, null);
    }
    public function showCategory(ProjectCategory $projectCategory)
    {
        $projectCategory = $this->showWithRelations($projectCategory, ['parent:id,name','tags']);
        return new ProjectCategoryResource($projectCategory);
    }

    public function getOptions()
    {
        $categories = $this->model->query()->select('id', 'name')->orderBy('name')->get();
        return $categories;
    }

}
