<?php

namespace App\Repositories\Contracts\Content;

use App\Models\Market\Comment;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ListableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface CommentRepositoryInterface extends
    ShowableRepositoryInterface,
    DeletableRepositoryInterface,
    CreatableRepositoryInterface,
    ListableRepositoryInterface
{
    public function getComments(): Paginator;
    public function showComment(Comment $comment): Comment;
}