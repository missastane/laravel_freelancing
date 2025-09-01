<?php

namespace App\Repositories\Eloquent\Market;

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

    protected function formatCategories($categories)
    {
        $categories->getColletion()->each(function ($item) {
            $item->makeHidden('status', 'show_in_menu', 'parent_id');
        })->append(['status_value', 'show_in_menu_value']);
    }
    public function getCategoies(): Paginator
    {
        $projectCategories = $this->all(['parent:id,name']);
        $this->formatCategories($projectCategories);
        return $projectCategories;

    }
    public function searchCategories(string $search): Paginator
    {
        $catgories = $this->model->where('name', 'LIKE', "%" . $search . "%")
            ->orWhere('description', 'LIKE', '%' . $search . '%')->with('parent:id,name')
            ->orderBy('name')
            ->simplePaginate(15);
        $this->formatCategories($catgories);
        return $catgories;
    }
    public function showCategory(ProjectCategory $projectCategory): ProjectCategory
    {
        $projectCategory = $this->showWithRelations($projectCategory, ['parent:id,name']);
        $projectCategory->makeHidden(['status', 'show_in_menu', 'parent_id'])->append(['status_value', 'show_in_menu_value']);
        return $projectCategory;
    }

    public function getOptions()
    {
        $categories = $this->model->query()->select('id', 'name')->orderBy('name')->get();
        return $categories;
    }

}
