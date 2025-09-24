<?php

namespace App\Http\Services\DisputeRequest;

use App\Models\User\ArbitrationRequest;
use App\Repositories\Contracts\User\ArbitrationRequestRepositoryInterface;

class ArbitrationRequestService
{
    public function __construct(
        protected ArbitrationRequestRepositoryInterface $arbitrationRequestRepository
    ) {
    }

    public function getAllRequestsByFilter(?string $status = null)
    {
        return $this->arbitrationRequestRepository->getAllByFilter($status);
    }

    public function showRequest(ArbitrationRequest $arbitrationRequest)
    {
        return $this->arbitrationRequestRepository->showArbitrationRequest($arbitrationRequest);
    }
}