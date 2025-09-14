<?php

namespace App\Policies;

use App\Models\Market\File;
use App\Models\User\User;
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
        protected OrderItemRepositoryInterface $orderItemRepository
    ) {
        //
    }

    public function deleteFile(User $user, File $file)
    {
        return $file->uploaded_by == $user->id;
    }

    public function setAsFinalFile(User $user, File $file)
    {
        $messageId = $file->filable_id;
        $message = $this->messageRepository->findById($messageId);
        $orderId = $message->message_context_id;
        $order = $this->orderRepository->findById($orderId);
        $orderItem = $this->orderItemRepository->getUncompleteItem($order);
        return $orderItem && in_array($order->status, [1, 2]);
    }

}
