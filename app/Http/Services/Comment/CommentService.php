<?php

namespace App\Http\Services\Comment;

use App\Models\Market\Comment;
use App\Models\User\User;
use App\Repositories\Contracts\Content\CommentRepositoryInterface;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;

class CommentService
{
    protected User $user;
    public function __construct(
        protected CommentRepositoryInterface $commentRepository
    ) {
        $this->user = auth()->user();
    }

    public function getComments(): Paginator
    {
        return $this->commentRepository->getComments();
    }
    public function showComment(Comment $comment): Comment
    {
        return $this->commentRepository->showComment($comment);
    }
    public function addComment(
        array $data,
        string $commentable,
        int $commentableId,
        ?int $seen,
        ?int $approved,
        ?int $status,
    ): Comment {

        return $this->commentRepository->create([
            'user_id' => $this->user->id,
            'comment' => $data['comment'],
            'commentable_type' => $commentable,
            'commentable_id' => $commentableId,
            'seen' => $seen,
            'approved' => $approved,
            'status' => $status
        ]);

    }
    public function answerComment(
        Comment $comment,
        array $data,
        ?int $seen,
        ?int $approved,
        ?int $status,
    ): Comment {

        return $this->commentRepository->create([
            'user_id' => $this->user->id,
            'parent_id' => $comment->id,
            'comment' => $data['comment'],
            'commentable_type' => $comment->commentable_type,
            'commentable_id' => $comment->commentable_id,
            'seen' => $seen,
            'approved' => $approved,
            'status' => $status
        ]);

    }
    public function toggleApprovedComment(Comment $comment): string|null
    {
        $comment->approved = $comment->approved == 1 ? 2 : 1;
        if ($comment->save()) {
            $message = $comment->approved == 1 ?
                'نظر با موفقیت تأیید شد' :
                'تأییدیه نظر با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }
    public function toggleSeenComment(Comment $comment): string|null
    {
        $comment->seen = $comment->seen == 1 ? 2 : 1;
        if ($comment->save()) {
            $message = $comment->seen == 1 ?
                'وضعیت نمایش نظر با موفقیت فعال شد' :
                'وضعیت نمایش نظر با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }
    public function toggleStatusComment(Comment $comment): string|null
    {
        $comment->status = $comment->status == 1 ? 2 : 1;
        if ($comment->save()) {
            $message = $comment->status == 1 ?
                'نظر با موفقیت فعال شد' :
                'نظر با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }

    public function deleteComment(Comment $comment)
    {
        return $this->commentRepository->delete($comment);
    }
}