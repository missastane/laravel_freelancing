<?php

namespace App\Http\Services\FileManagemant;

use App\Http\Services\File\FileService;
use App\Models\Market\File;
use App\Models\Market\FinalFile;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Repositories\Contracts\Market\FileRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class FileManagementService
{
    use ApiResponseTrait;
    public function __construct(
        protected FileRepositoryInterface $fileRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected FileService $fileService
    ) {

    }

    // this method is neccessary for isFinalFileMethod
    public function options(Order $order)
    {
        return $this->orderRepository->getOrderItems($order);
    }
    public function isFinalFile(File $file, Request $request)
    {
        try {
            $userId = auth()->id();
            $file->update(['is_final_delivery' => $request->isFinalFile]);
            $finalFile = FinalFile::create([
                'order_item_id' => $request->order_item_id,
                'file_id' => $file->id,
                'freelancer_id' => $userId,
                'delivered_at' => now(),
            ]);
            // broadcast
            return $this->success(null, 'فایل با موفقیت برای کارفرما ارسال شد', 201);

        } catch (Exception $e) {
            return $this->error();
        }
    }

    // public function createFile(array $data): File
    // {
    //     return $this->fileRepository->create($data);
    // }
    public function deleteFile(File $file)
    {
        $this->fileService->deleteFile($file->file_path);
        $this->fileRepository->delete($file);
    }

}