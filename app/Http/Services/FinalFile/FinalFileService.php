<?php

namespace App\Http\Services\FinalFile;

use App\Models\Market\FinalFile;
use App\Repositories\Contracts\Market\FinalFileRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Eloquent\Market\OrderItemRepository;
use Illuminate\Support\Facades\DB;

class FinalFileService
{
    public function __construct(
        protected FinalFileRepositoryInterface $finalFileRepository,
        protected fileItemApproveService $fileItemApproveService,
        protected FileItemRejectService $fileItemRejectService,
        protected OrderItemRepositoryInterface $orderItemRepository
    ) {
    }

    public function revisionFileItem(FinalFile $finalFile, array $data): FinalFile
    {
        return DB::transaction(function () use ($finalFile, $data) {
            $this->finalFileRepository->update($finalFile, [
                'status' => 3,
                'employer_id' => auth()->id(),
                'revision_at' => now(),
                'revision_note' => $data['revision_note']
            ]);
            $this->orderItemRepository->update($finalFile->orderItem, ['status' => 2]);
            return $finalFile;
        });
    }

    public function approveFileItem(FinalFile $finalFile)
    {
        return $this->fileItemApproveService->approveFileItem($finalFile);
    }

    public function rejectFileItem(FinalFile $finalFile, array $data)
    {
        return $this->fileItemRejectService->rejectFile($finalFile, $data);
    }
}