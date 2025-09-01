<?php

namespace App\Http\Services\Tag;

use App\Models\Content\Tag;
use App\Repositories\Contracts\Content\TagRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class TagService
{
    public function __construct(protected TagRepositoryInterface $tagRepository)
    {
    }

    public function getTags(): Paginator
    {
        return $this->tagRepository->all(['taggables'], 'id', 'desc');
    }

    public function searchTag(string $search): Paginator
    {
        return $this->tagRepository->searchTag($search);
    }

    public function showTag(Tag $tag): Tag
    {
        return $this->tagRepository->showTag($tag);
    }

    public function storeTag(array $data): Tag
    {
        return $this->tagRepository->create($data);
    }

    public function updateTag(Tag $tag, array $data)
    {
        return $this->tagRepository->update($tag,$data);
    }

    public function deleteTag(Tag $tag)
    {
        return $this->tagRepository->delete($tag);
    }
}