<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Conversation;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface ConversationRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface
{
    public function getConversationIfExists(int $freelancerId, int $employerId, string $context, int $contextId): Conversation|null;
    public function getConversationMessages(Conversation $conversation);
}