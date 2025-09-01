<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User\Notification;
use App\Models\User\User;
use App\Repositories\Contracts\User\NotificationRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }
    public function getUserNotifications(int $userId): Paginator
    {
        $notifications = $this->model->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->orderBy('created_at', 'desc')->simplePaginate(15);
        return $notifications;
    }

}