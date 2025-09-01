<?php

namespace App\Repositories\Eloquent\Content;

use App\Models\Market\Comment;
use App\Repositories\Contracts\Content\CommentRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasCRUDTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasListTrait;
use App\Traits\HasShowTrait;
use Illuminate\Contracts\Pagination\Paginator;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
    use HasListTrait;
    use HasShowTrait;
    use HasCreateTrait;
    use HasDeleteTrait;
    public function __construct(Comment $model)
    {
        parent::__construct($model);
    }
    public function getComments(): Paginator
    {
        $comments = $this->all(['user:id,first_name,last_name','parent:id,comment']);
        return $comments;
    }
    public function showComment(Comment $comment): Comment
    {
        return $this->showWithRelations($comment,['user:id,first_name,last_name','parent:id,comment']);
    }
}