<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\Conversation;
use App\Repositories\Contracts\Market\ConversationRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;

class ConversationRepository extends BaseRepository implements ConversationRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;

    public function __construct(Conversation $model)
    {
        parent::__construct($model);
    }
    public function getConversationIfExists(string $context, int $contextId): Conversation|null
    {
        $conversation = $this->model->where([
            'conversation_context' => $context,
            'conversation_context_id' => $contextId
        ])->first();
        return $conversation;
    }

    public function getConversationMessages(Conversation $conversation)
    {
        $conversation = $this->showWithRelations($conversation,['employer:id,username', 'freelancer:id,username']);
        $messages = $this->showWithRelations($conversation->messages,['sender:id,username', 'parent:id,message', 'files:id,file_name,file_path,mime_type']);
        return [
            'conversation' => $conversation,
            'messages' => $messages
        ];
    }


}