<?php

namespace App\Policies;

use App\Models\Market\File;
use App\Models\Market\Message;
use App\Models\Market\Order;
use App\Models\User\User;
use App\Repositories\Contracts\Market\FinalFileRepositoryInterface;
use App\Repositories\Contracts\Market\MessageRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;

class FilePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct(
        protected MessageRepositoryInterface $messageRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected FinalFileRepositoryInterface $finalFileRepository
    ) {
        //
    }

    public function deleteFile(User $user, File $file)
    {
        return $file->uploaded_by == $user->id;
    }

    public function setAsFinalFile(User $user, File $file)
    {
        if ($file->filable_type !== Message::class) {
            return false;
        }
        $messageId = $file->filable_id;
        $message = $this->messageRepository->findById($messageId);
        if ($message->message_context !== Order::class) {
            return false;
        }
        $orderId = $message->message_context_id;
        $order = $this->orderRepository->findById($orderId);
        $orderItem = $this->orderItemRepository->getUncompleteItem($order);
        $finalFileAlreadyExist = $this->finalFileRepository->findByFileId($file);
        return
            $file->uploaded_by == $user->id &&
            $orderItem &&
            in_array($order->status, [1, 2]) &&
            !$finalFileAlreadyExist;
    }

    public function canDownload(User $user, File $file)
    {
        $messageId = $file->filable_id;
        $message = $this->messageRepository->findById($messageId);
        $orderId = $message->message_context_id;
        $order = $this->orderRepository->findById($orderId);
        return $user->id == $order->freelancer_id || $user->id == $order->employer_id || $user->active_role === 'admin';
    }

}
