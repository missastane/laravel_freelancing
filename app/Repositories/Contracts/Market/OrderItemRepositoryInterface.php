<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\FinalFile;
use App\Models\Market\OrderItem;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface OrderItemRepositoryInterface extends ShowableRepositoryInterface,CreatableRepositoryInterface,UpdatableRepositoryInterface
{

}