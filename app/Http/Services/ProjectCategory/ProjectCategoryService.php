<?php

namespace App\Http\Services\ProjectCategory;

use App\Http\Services\Public\MediaStorageService;
use App\Http\Services\Tag\TagStorageService;
use App\Models\Market\ProjectCategory;
use App\Repositories\Contracts\Market\ProjectCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProjectCategoryService
{

    public function __construct(
        protected MediaStorageService $mediaStorageService,
        protected TagStorageService $tagStorageService,
        protected ProjectCategoryRepositoryInterface $projectCategoryRepository
    ) {

    }

    public function getCategories()
    {
        $cacheKey = 'project_categories';
        return Cache::rememberForever($cacheKey, fn() => $this->projectCategoryRepository->getCategoies());
    }
    public function searchCategory(string $search)
    {
        return $this->projectCategoryRepository->searchCategories($search);
    }
    public function showCategory(ProjectCategory $projectCategory)
    {
        return $this->projectCategoryRepository->showCategory($projectCategory);
    }
    public function storeCategory(array $data): ProjectCategory
    {
        $result = DB::transaction(function () use ($data) {
            $data['image'] = $this->mediaStorageService->storeMultipleImages($data['image'], "images/project-category");
            $projectCategory = $this->projectCategoryRepository->create($data);
            $this->tagStorageService->storeTagsForFirstTime($projectCategory, $data['tags']);
            return $projectCategory;
        });
        if ($result) {
            Cache::forget('project_categories');
        }
        return $result;
    }
    public function updateCategory(ProjectCategory $projectCategory, array $data): ProjectCategory
    {
        $result = DB::transaction(function () use ($projectCategory, $data) {
            $data['image'] = $this->mediaStorageService->updateImageIfExists(
                $data['image'],
                $projectCategory->image,
                "images/project-category",
                null
            );
            $this->projectCategoryRepository->update($projectCategory, $data);
            $this->tagStorageService->syncTags($projectCategory, $data['tags']);
            return $projectCategory;
        });
        if ($result) {
            Cache::forget('project_categories');
        }
        return $result;
    }
    public function toggleShowInMenu(ProjectCategory $projectCategory): string|bool
    {
        $projectCategory->show_in_menu = $projectCategory->show_in_menu == 1 ? 2 : 1;
        if ($projectCategory->save()) {
            Cache::forget('project_categories');
            $message = $projectCategory->show_in_menu == 1
                ? 'وضعیت نمایش دسته بندی با موفقیت فعال شد'
                : 'وضعیت نمایش دسته بندی با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }
    public function toggleStatus(ProjectCategory $projectCategory): string|bool
    {
        $projectCategory->status = $projectCategory->status == 1 ? 2 : 1;
        if ($projectCategory->save()) {
            Cache::forget('project_categories');
            $message = $projectCategory->status == 1
                ? 'دسته بندی پروژه با موفقیت فعال شد'
                : 'دسته بندی پروژه با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }

    public function deleteCategory(ProjectCategory $projectCategory)
    {
        $result = $this->projectCategoryRepository->delete($projectCategory);
        if ($result) {
            Cache::forget('project_categories');
        }
        return $result;
    }
}