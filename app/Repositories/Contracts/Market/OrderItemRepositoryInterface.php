<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\FinalFile;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface OrderItemRepositoryInterface extends ShowableRepositoryInterface, CreatableRepositoryInterface, UpdatableRepositoryInterface
{
    public function getOrderItems(Order $order);
    public function getFirstPendingItem(Order $order);
    public function getUncompleteItem(Order $order);
    public function hasUndeliveredItem(Order $order);
    public function getUnApprovedOrderItemsExecpetOne(Order $order, ?OrderItem $except = null);
}