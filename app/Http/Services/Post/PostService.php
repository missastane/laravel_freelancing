<?php

namespace App\Http\Services\Post;

use App\Http\Services\Public\MediaStorageService;
use App\Http\Services\Tag\TagStorageService;
use App\Models\Content\Post;
use App\Repositories\Contracts\Content\PostCategoryRepositoryInterface;
use App\Repositories\Contracts\Content\PostRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PostService
{

    public function __construct(
        protected MediaStorageService $mediaStorageService,
        protected TagStorageService $tagStorageService,
        protected PostRepositoryInterface $postRepository,
        protected PostCategoryRepositoryInterface $postCategoryRepository

    ) {
    }

    public function getPosts()
    {
        return $this->postRepository->getPosts();
    }
    public function searchPosts(string $search)
    {
        return $this->postRepository->searchPosts($search);
    }
    public function showPost(Post $post)
    {
        return $this->postRepository->showPost($post);
    }
    public function options()
    {
        return[
            'posts' => $this->postRepository->getPostOptions(),
            'postCategories' => $this->postCategoryRepository->getCategoryOptions()
        ];
    }
    public function storePost(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            date_default_timezone_set('Iran');
            $realTimestamp = substr($data['published_at'], 0, 10);
            $data['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $data['image'] = $this->mediaStorageService->storeMultipleImages($data['image'], "images/post");
            $data['author_id'] = auth()->user()->id;
            $post = $this->postRepository->create($data);
            $this->tagStorageService->storeTagsForFirstTime($post, $data['tags']);
            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                Post::class,
                $post->id,
                "files/posts/{$post->id}",
                "public"
            );
            return $post;
        });
    }
    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($data, $post) {
            date_default_timezone_set('Iran');
            $realTimestamp = substr($data['published_at'], 0, 10);
            $data['published_at'] = date("Y-m-d H:i:s", (int) $realTimestamp);
            $data['image'] = $this->mediaStorageService->updateImageIfExists($data['image'], $post->image, "images/post", null);
            $this->postRepository->update($post, $data);
            $this->tagStorageService->syncTags($post, $data['tags']);
            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                Post::class,
                $post->id,
                "files/posts/{$post->id}",
                "public"
            );
            return $post;
        });
    }

    public function changeStatus(Post $post): string|null
    {
        $post->status = $post->status == 1 ? 2 : 1;
        if ($post->save()) {
            $message = $post->status == 1
                ? 'پست با موفقیت فعال شد'
                : 'پست با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }

    public function deletePost(Post $post)
    {
        return $this->postRepository->delete($post);
    }
}