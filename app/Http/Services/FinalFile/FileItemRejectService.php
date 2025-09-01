<?php

namespace App\Http\Services\FinalFile;

use App\Events\AddDisputeRequestEvent;
use App\Models\Market\FinalFile;
use App\Models\Market\OrderItem;
use App\Models\User\User;
use App\Repositories\Contracts\Market\FinalFileRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\User\DisputeRequestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FileItemRejectService
{
    protected User $user;
    public function __construct(
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected FinalFileRepositoryInterface $finalFileRepository,
        protected DisputeRequestRepositoryInterface $disputeRequestRepository
    ) {
        $this->user = auth()->user();
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
    protected function createDisputeRequest(FinalFile $finalFile, array $data)
    {
        $this->disputeRequestRepository->create([
            'order_item_id' => $finalFile->order_item_id,
            'user_type' => 1,
            'raised_by' => $this->user->id,
            'reason' => $data['rejected_note'],
        ]);
    }
    public function rejectFile(FinalFile $finalFile, array $data)
    {
        return DB::transaction(function () use ($finalFile, $data) {
            $this->finalFileRepository->update($finalFile, [
                'status' => 3,
                'rejected_type' => 2,
                'rejected_note' => $data['rejected_note']
            ]);
            $orderItem = $finalFile->orderItem;
            $this->updateOrderItem($orderItem->first(), $data);
            $this->createDisputeRequest($finalFile, $data);

            // notify to freelancer and employer that this orderitem is locked
            event(new AddDisputeRequestEvent($finalFile->freelancer->first(), $finalFile->employer->first(), $finalFile->employer->first(), $finalFile->order_item_id));
        });


    }
}