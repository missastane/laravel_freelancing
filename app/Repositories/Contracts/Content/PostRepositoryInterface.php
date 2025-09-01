<?php

namespace App\Repositories\Contracts\Content;

use App\Models\Content\Post;
use App\Repositories\Contracts\BaseRepositoryInterface;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    public function getPosts();
    public function getPostOptions();
    public function searchPosts(string $search);
    public function showPost(Post $post);
}