<?php

namespace App\Http\Services\FinalFile;

use App\Events\AddDisputeRequestEvent;
use App\Models\Market\FinalFile;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\User\User;
use App\Repositories\Contracts\Market\ConversationRepositoryInterface;
use App\Repositories\Contracts\Market\FinalFileRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\User\DisputeRequestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FileItemRejectService
{
    public function __construct(
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected FinalFileRepositoryInterface $finalFileRepository,
        protected DisputeRequestRepositoryInterface $disputeRequestRepository,
        protected ConversationRepositoryInterface $conversationRepository
    ) {
        
    }

    protected function updateOrderItem(OrderItem $orderItem, array $data)
    {
        $this->orderItemRepository->update($orderItem, [
            'status' => 5,
            'locked_by' => 1,
            'locked_reason' => 3,
            'locked_note' => $data['rejected_note'],
            'locked_at' => now()
        ]);
    }
    protected function lockConversation(Order $order)
    {
        $conversation = $this->conversationRepository->getConversationIfExists(
            $order->freelancer_id,
            $order->employer_id,
            Order::class,
            $order->id
        );
        $this->conversationRepository->update($conversation,['status' => 2]);
    }
    protected function createDisputeRequest(FinalFile $finalFile, array $data)
    {
        $this->disputeRequestRepository->create([
            'order_item_id' => $finalFile->order_item_id,
            'final_file_id' => $finalFile->id,
            'user_type' => 1,
            'raised_by' => auth()->id(),
            'reason' => $data['rejected_note'],
        ]);
    }
    public function rejectFile(FinalFile $finalFile, array $data)
    {
        return DB::transaction(function () use ($finalFile, $data) {
            $this->finalFileRepository->update($finalFile, [
                'employer_id' => auth()->id(),
                'status' => 4, //rejected
                'rejected_note' => $data['rejected_note'],
                'rejected_at' => now()
            ]);
            $orderItem = $finalFile->orderItem;
            $order = $orderItem->order;
            $this->updateOrderItem($orderItem, $data);
            $this->lockConversation($order);
            $this->createDisputeRequest($finalFile, $data);

            // notify to freelancer and employer that this orderitem is locked
            event(new AddDisputeRequestEvent($finalFile->freelancer, $order->employer, $order->employer, $finalFile->order_item_id));
        });


    }
}