<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\NotificationResource;
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
    public function getUserNotifications(int $userId)
    {
        $notifications = $this->model->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)->whereNull('read_at')
            ->orderBy('created_at', 'desc')->paginate(15);
        return new BaseCollection($notifications, NotificationResource::class,null);
    }

}