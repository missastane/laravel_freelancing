<?php

namespace App\Repositories\Eloquent\Content;

use App\Http\Resources\Content\PostResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Content\Post;
use App\Models\Content\PostCategory;
use App\Repositories\Contracts\Content\PostRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
   use HasCRUDTrait;
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }
     public function getPosts()
    {
        $posts = $this->all(['postCategory:id,name', 'author:id,first_name,last_name', 'tags:id,name','files'],'id','desc');
        return New BaseCollection($posts,PostResource::class,null);
    }
    public function searchPosts(string $search)
    {
        $posts = $this->model->where('title', 'LIKE', "%" . $search . "%")
            ->with('postCategory:id,name', 'author:id,first_name,last_name', 'tags:id,name','files')
            ->orderBy('title', 'asc')->paginate(15);
            
        return New BaseCollection($posts,PostResource::class,null);
    }
    public function showPost(Post $post)
    {
        $post = $this->showWithRelations($post, ['postCategory:id,name', 'author:id,first_name,last_name', 'tags:id,name','files']);
        foreach($post->files() as $file){
            \Log::info($file);
        }
        return new PostResource($post);
    }

    public function getPostOptions()
    {
        $posts = $this->model->query()->select('id', 'title')->get();
        return $posts;
    }


}