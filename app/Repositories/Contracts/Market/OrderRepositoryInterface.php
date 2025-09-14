<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Order;
use App\Models\User\User;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ListableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface OrderRepositoryInterface extends CreatableRepositoryInterface, ShowableRepositoryInterface, UpdatableRepositoryInterface
{
    public function getAllOrders(array $data): Paginator;
    public function getUserOrders(?User $user, array $data): Paginator;
    public function getOrderFinalFiles(Order $order);
    public function findById(int $orderId);
    public function showOrder(Order $order): Order;
    public function getUserCompletedOrders(User $targetUser = null): Paginator;
}