<?php

namespace App\Http\Services\Chat;

use App\Events\NewMessageEvent;
use App\Http\Services\File\FileService;
use App\Http\Services\FileManagemant\FileManagementService;
use App\Http\Services\Public\MediaStorageService;
use App\Models\Market\Conversation;
use App\Models\Market\File;
use App\Models\Market\Message;
use App\Models\Market\Proposal;
use App\Models\User\User;
use App\Repositories\Contracts\Market\ConversationRepositoryInterface;
use App\Repositories\Contracts\Market\MessageRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChatService
{
    // just employers can send message to all freelancers. then create a conversation begins with employers

    public function __construct(
        protected ConversationRepositoryInterface $conversationRepository,
        protected MessageRepositoryInterface $messageRepository,
        protected MediaStorageService $mediaStorageService,
        protected FileManagementService $fileManagementService
    ) {
    }

    public function getConversationMessages(Conversation $conversation)
    {
        return $this->conversationRepository->getConversationMessages($conversation);
    }
    public function getOrCreateConversationForProposal(Proposal $proposal)
    {
        $conversation = $this->conversationRepository->getConversationIfExists(
            $proposal->freelancer_id,
            $proposal->project->user_id,
            Proposal::class,
            $proposal->id
        );
        if ($conversation) {
            return $conversation;
        }
        if (auth()->user()->active_role === 'employer') {
            return $this->createConversation(
                $proposal->freelancer,
                $proposal
            );
        } else {
            return null;
        }
    }


    public function createConversation($freelancer, $context)
    {
        try {
            $employer = auth()->user();
            $conversation = $this->conversationRepository->create([
                'employer_id' => $employer->id,
                'employee_id' => $freelancer->id,
                'conversation_context' => get_class($context),
                'conversation_context_id' => $context->id
            ]);
            return $conversation;
        } catch (Throwable $e) {
            throw new Exception('خطا در ایجاد مکالمه جدید', 500);
        }
    }
    public function createNewMessage(Conversation $conversation, array $data, int $userId, int $parentId = null)
    {
        try {
            $newMessage = $this->messageRepository->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userId,
                'message' => $data['message'],
                'sent_date' => now(),
                'parent_id' => $parentId,
                'message_context' => $conversation->conversation_context,
                'message_context_id' => $conversation->conversation_context_id
            ]);
            return $newMessage;
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500);
        }

    }
    public function storeMessageFiles(Message $newMessage, array $data, int $conversationId)
    {
        $this->mediaStorageService->storeMultipleFiles(
            $data['files'],
            Message::class,
            $newMessage->id,
            "files/messages/$newMessage->id"
        );
    }
    public function sendMessage(Conversation $conversation, array $data)
    {
        $user = auth()->user();
        $newMessage = DB::transaction(function () use ($conversation, $data, $user) {
            $message = $this->createNewMessage($conversation, $data, $user->id, null);
            $this->storeMessageFiles($message, $data, $conversation->id);
            return $message;
        });

        // broadcasting message
        broadcast(new NewMessageEvent($newMessage))->toOthers();
        return $this->messageRepository->showMessage($newMessage);
    }

    public function replyToMessage(Message $message, array $data)
    {
        $user = auth()->user();
        $newMessage = DB::transaction(function () use ($message, $data, $user) {
            $answeredMessage = $this->createNewMessage($message->conversation, $data, $user->id, $message->id);
            $this->storeMessageFiles($answeredMessage, $data, $message->conversation_id);
            return $answeredMessage;
        });
        // broadcasting message
        broadcast(new NewMessageEvent($newMessage))->toOthers();
        \Log::info($newMessage->id);
        return $this->messageRepository->showMessage($newMessage);
    }

    public function setAsFinalFile(File $file)
    {
        return $this->fileManagementService->setAsFinalFile($file);
    }
    public function deleteMessageFile(File $file)
    {
        return $this->fileManagementService->deleteFile($file);
    }

    public function deleteMessage(Message $message)
    {
        return $this->messageRepository->delete($message);
    }
}