<?php

namespace App\Http\Services\FinalFile;

use App\Models\Market\FinalFile;
use App\Repositories\Contracts\Market\FinalFileRepositoryInterface;

class FinalFileService
{
    public function __construct(
        protected FinalFileRepositoryInterface $finalFileRepository,
        protected fileItemApproveService $fileItemApproveService,
        protected FileItemRejectService $fileItemRejectService
    ) {
    }

    public function revisionFileItem(FinalFile $finalFile, array $data): FinalFile
    {
        $this->finalFileRepository->update($finalFile, [
            'status' => 3,
            'employer_id' => auth()->id(),
            'rejected_type' => 1,
            'revision_at' => now(),
            'revision_note' => $data
        ]);
        return $finalFile;
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