<?php

namespace App\Http\Services\Order;

use App\Http\Services\FinalFile\FinalFileService;
use App\Models\Market\Order;
use App\Models\User\User;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class OrderService
{
    public function __construct(
        protected FinalFileService $finalFileService,
        protected OrderRepositoryInterface $orderRepository
    ) {
    }
    public function getAllOrders(string $status)
    {
        return $this->orderRepository->getAllOrders($status);
    }

    public function getUserOrders(?User $user, ?string $status = null)
    {
        return $this->orderRepository->getUserOrders($user, $status);
    }

    public function getOrderFinalFiles(Order $order)
    {
        return $this->orderRepository->getOrderFinalFiles($order);
    }
   

    public function showOrder(Order $order)
    {
        return $this->orderRepository->showOrder($order);
    }

}