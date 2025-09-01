<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\ProjectCategory;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface ProjectCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getCategoies();
    public function searchCategories(string $search);
    public function showCategory(ProjectCategory $projectCategory);
    public function getOptions();
}