<?php

namespace App\Repositories\Contracts\Market;

use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface FinalFileRepositoryInterface extends
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface,
    CreatableRepositoryInterface
{
    
}