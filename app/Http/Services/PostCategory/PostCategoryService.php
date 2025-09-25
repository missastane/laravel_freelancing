<?php

namespace App\Http\Services\PostCategory;

use App\Http\Services\Public\MediaStorageService;
use App\Http\Services\Tag\TagStorageService;
use App\Models\Content\PostCategory;
use App\Repositories\Contracts\Content\PostCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class PostCategoryService
{
    public function __construct(
        protected MediaStorageService $mediaStorageService,
        protected TagStorageService $tagStorageService,
        protected PostCategoryRepositoryInterface $postCategoryRepository
    ) {

    }
    public function getCategories()
    {
        return $this->postCategoryRepository->getCategories();
    }
    public function searchCategories(string $search)
    {
        return $this->postCategoryRepository->searchCategories($search);
    }
    public function showCategory(PostCategory $postCategory)
    {
        return $this->postCategoryRepository->showCategory($postCategory);
    }
    public function options()
    {
        return $this->postCategoryRepository->getCategoryOptions();
    }
    public function storeCategory(array $data)
    {
        return DB::transaction(function () use ($data) {

            $data['image'] = $this->mediaStorageService->storeMultipleImages($data['image'], "images/post-category");
            $postCategory = $this->postCategoryRepository->create($data);
            $this->tagStorageService->storeTagsForFirstTime($postCategory, $data['tags']);
            return $postCategory;
        });
    }

    public function updateCategory(PostCategory $category, array $data): PostCategory
    {
        return DB::transaction(function () use ($category, $data) {

            // update image
            $data['image'] = $this->mediaStorageService
                ->updateImageIfExists(
                    $data['image'],
                    $category->image,
                    "images/post-category",
                    null
                );

            // update category
            $this->postCategoryRepository->update($category, $data);

            // update tags
            $this->tagStorageService->syncTags($category, $data['tags']);

            return $category;
        });
    }

    public function delete(PostCategory $postCategory)
    {
        return $this->postCategoryRepository->delete($postCategory);
    }

}