<?php

namespace App\Repositories\Eloquent\Market;

use App\Http\Resources\Market\ConversationResource;
use App\Http\Resources\Market\MessageResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
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
    public function getConversationIfExists(int $freelancerId, int $employerId): Conversation|null
    {
        
        $conversation = $this->model->where([
            'employer_id' => $employerId,
            'employee_id' => $freelancerId
        ])->first();
        return $conversation;
    }

    public function getConversationMessages(Conversation $conversation)
    {
        $conversation = $this->showWithRelations($conversation,['employer:id,username', 'freelancer:id,username']);
        $messages = $conversation->messages->load(['sender:id,username', 'parent:id,message', 'files:id,file_name,file_path,mime_type']);
        return new ConversationResource($conversation);
    }


}