<?php

namespace App\Repositories\Eloquent\Content;

use App\Http\Resources\Market\AdminCommentResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
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
    public function getComments()
    {
        $comments = $this->all(['user:id,first_name,last_name,username','parent']);
        return new BaseCollection($comments,AdminCommentResource::class,null);
    }
    public function showComment(Comment $comment)
    {
        $result = $this->showWithRelations($comment,['user:id,first_name,last_name,username','parent:id,comment,created_at,user_id']);
        return new AdminCommentResource($result);
    }
}