<?php

namespace App\Repositories\Contracts\User;

use App\Models\User\ArbitrationRequest;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;

interface ArbitrationRequestRepositoryInterface extends 
CreatableRepositoryInterface,
ShowableRepositoryInterface
{
    public function showArbitrationRequest(ArbitrationRequest $arbitrationRequest);
}