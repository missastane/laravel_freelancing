<?php

namespace App\Repositories\Eloquent\Market;

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
    public function showMessage(Message $message): Message
    {
        return $this->showWithRelations($message, ['files']);
    }

}