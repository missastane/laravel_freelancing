<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Conversation;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;

interface ConversationRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface
{
    public function getConversationIfExists(string $context, int $contextId):Conversation|null;
    public function getConversationMessages(Conversation $conversation);
}