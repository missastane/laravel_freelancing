<?php

namespace App\Repositories\Contracts\Content;

use App\Models\Content\PostCategory;
use App\Repositories\Contracts\BaseRepositoryInterface;

interface PostCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getCategoryOptions();
    public function searchCategories(string $search);
    public function showCategory(PostCategory $postCategory);
    public function getCategories();
}