<?php

namespace App\Http\Services\Tag;

use App\Models\Content\Tag;
use App\Repositories\Contracts\Content\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class TagStorageService
{
    public function __construct(protected TagRepositoryInterface $tagRepository)
    {
    }
    public function storeTagsForFirstTime(Model $model, ?array $data): void
    {
        if (empty($data)) {
            return;
        }
        foreach ($data as $tagName) {
            $tag = $this->tagRepository->firstOrCreateTag(['name' => $tagName]);
            $this->tagRepository->attachTagToModel($model, $tag);
        }
    }

    public function syncTags(Model $model, ?array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $tagIds = [];
        foreach ($tags as $tagName) {
            $tag = $this->tagRepository->firstOrCreateTag(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }
        $this->tagRepository->syncTagsToModel($model, $tagIds);
    }


}