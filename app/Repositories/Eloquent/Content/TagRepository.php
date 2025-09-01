<?php

namespace App\Repositories\Eloquent\Content;

use App\Models\Content\Tag;
use App\Repositories\Contracts\Content\TagRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }
    public function showTag(Tag $tag): Tag
    {
        $tag = $this->showWithRelations($tag, ['taggables']);
        return $tag;
    }
    public function searchTag(string $search): Paginator
    {
        $tags = $this->model->where('name', 'LIKE', "%" . $search . "%")->with('taggables')->orderBy('name')->simplePaginate(15);
        return $tags;
    }

    public function firstOrCreateTag(array $data)
    {
        return $this->model->firstOrCreate($data);
    }
    public function attachTagToModel(Model $model, $tag)
    {
        return $model->tags()->attach($tag);
    }

    public function syncTagsToModel(Model $model, array $tagIds)
    {
        return $model->tags()->sync($tagIds);
    }

}