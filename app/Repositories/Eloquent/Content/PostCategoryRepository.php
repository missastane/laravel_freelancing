<?php

namespace App\Repositories\Eloquent\Content;

use App\Http\Resources\Content\PostCategoryResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Content\PostCategory;
use App\Repositories\Contracts\Content\PostCategoryRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;

class PostCategoryRepository extends BaseRepository implements PostCategoryRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(PostCategory $model)
    {
        parent::__construct($model);
    }
    public function searchCategories(string $search)
    {
        $categories =$this->model->where('name', 'LIKE', "%" . $search . "%")->with('tags')->orderBy('name')->paginate(15);
        return new BaseCollection($categories,PostCategoryResource::class,null);
    }

    public function showCategory(PostCategory $postCategory)
    {
        $category = $this->showWithRelations($postCategory,['tags']);
        return new PostCategoryResource($category);
    }

    public function getCategoryOptions()
    {
        $categories = $this->model->query()->select(['id', 'name'])->get();
        return $categories;
    }

    public function getCategories()
    {
        $categories = $this->all(['tags']);
        return new BaseCollection($categories,PostCategoryResource::class,null);
    }
}