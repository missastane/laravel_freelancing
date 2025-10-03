<?php

namespace App\Http\Services\FileManagemant;

use App\Exceptions\Market\NotAllowedToSetFinalFile;
use App\Http\Services\File\FileService;
use App\Models\Market\File;
use App\Models\Market\FinalFile;
use App\Models\Market\Message;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Notifications\SendFinalFileNotification;
use App\Repositories\Contracts\Market\FileRepositoryInterface;
use App\Repositories\Contracts\Market\MessageRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileManagementService
{
    use ApiResponseTrait;
    public function __construct(
        protected FileRepositoryInterface $fileRepository,
        protected MessageRepositoryInterface $messageRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected FileService $fileService
    ) {

    }

    // this method is neccessary for isFinalFileMethod
    public function options(Order $order)
    {
        return $this->orderItemRepository->getOrderItems($order);
    }
    public function setAsFinalFile(File $file)
    {
        $messageId = $file->filable_id;
        $message = $this->messageRepository->findById($messageId);
        $orderId = $message->message_context_id;
        $order = $this->orderRepository->findById($orderId);
        $orderItem = $this->orderItemRepository->getUncompleteItem($order);

        $result = DB::transaction(function () use ($file,$order, $orderItem) {
            $userId = auth()->id();
            $this->fileRepository->update(
                $file,
                ['is_final_delivery' => 1] //yes
            );
            $finalFile = FinalFile::create([
                'order_item_id' => $orderItem->id,
                'file_id' => $file->id,
                'freelancer_id' => $userId,
                'delivered_at' => now(),
            ]);
            $this->orderItemRepository->update($orderItem, [
                'delivered_at' => now(),
                'status' => 3 //complete
            ]);
            $hasUndeliveredItems = $this->orderItemRepository->hasUndeliveredItem($order);

        if (! $hasUndeliveredItems) {
            // all items has been delivered
            $this->orderRepository->update($order, [
                'delivered_at' => now(),
            ]);
        }
            return $file;
        });
        // broadcast
        $employer = $order->employer;
        $project = $order->project;
        $employer->notify(new SendFinalFileNotification($project, "فایل پروژه {$project->title} توسط فریلنسر برای شما ارسال شد. لطفا بررسی بفرمایید"));
        return $result;
    }

    public function deleteFile(File $file)
    {
        $this->fileService->deleteFile($file->file_path);
        $this->fileRepository->delete($file);
    }

}