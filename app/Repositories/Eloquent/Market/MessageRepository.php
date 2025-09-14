<?php

namespace App\Repositories\Eloquent\Market;

use App\Http\Resources\Market\MessageResource;
use App\Models\Market\Message;
use App\Repositories\Contracts\Market\MessageRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasDeleteTrait;
    public function __construct(Message $model)
    {
        parent::__construct($model);
    }

    public function findById(int $messageId)
    {
        return $this->model->find($messageId);
    }

    public function showMessage(Message $message)
    {
       $result = $this->showWithRelations($message, ['files','parent']);
       return new MessageResource($result);
    }

}