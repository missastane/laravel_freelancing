<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\ProjectCategory;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface ProjectCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getCategoies() : Paginator;
    public function searchCategories(string $search) : Paginator;
    public function showCategory(ProjectCategory $projectCategory) : ProjectCategory;
    public function getOptions();
}