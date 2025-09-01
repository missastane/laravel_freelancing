<?php

namespace App\Repositories\Contracts\Content;

use App\Models\Content\Tag;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;

interface TagRepositoryInterface extends BaseRepositoryInterface
{
    public function showTag(Tag $tag) : Tag;
    public function searchTag(string $search) : Paginator;
    public function firstOrCreateTag(array $data);
    public function attachTagToModel(Model $model,$tag);
    public function syncTagsToModel(Model $model, array $tagIds);
}