<?php

namespace App\Policies;

use App\Models\Content\Post;
use App\Models\Market\Comment;
use App\Models\Market\Order;
use App\Models\User\User;

class CommentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function reply(User $user, Comment $comment)
    {
        $validTypes = [Order::class, Post::class];
        if (!in_array($comment->commentable_type, $validTypes)) {
            return false;
        }
        if ($comment->commentable_type === Order::class) {
            $order = Order::find($comment->commentable_id);
            if (!$order)
                return false;

            $isValidRole = match ($user->active_role) {
                'freelancer' => $user->id === $order->freelancer_id,
                'employer' => $user->id === $order->employer_id,
                default => false,
            };

            if (!$isValidRole)
                return false;
        }

        if ($comment->commentable_type === Comment::class) {
            if ($comment->approved != 1 || $comment->status != 1) {
                return false;
            }
        }
        if ($comment->user_id === $user->id) {
            return false;
        }
        return !$comment->replies()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function adminReply(User $user, Comment $comment)
    {
        if ($comment->commentable_type !== Post::class) {
            return false;
        }
        if ($comment->approved != 1 || $comment->status != 1) {
            return false;
        }
        return true;
    }


}
