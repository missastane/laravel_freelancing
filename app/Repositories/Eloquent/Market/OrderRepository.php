<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\User\User;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    use HasUpdateTrait;
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getAllOrders(array $data): Paginator
    {
        $orders = $this->model->filterByStatus($data)->with(['freelancer:id,first_name,last_name', 'employer:id,first_name,last_name', 'orderItems'])
            ->orderBy('created_at', 'desc')->simplePaginate(15);
        return $orders;
    }

    public function getUserOrders(?User $user, array $data): Paginator
    {
        $userId = $user->id ?? auth()->id();

        $query = match (auth()->user()->active_role) {
            'freelancer' => $this->model->where('freelancer_id', $userId),
            'employer' => $this->model->where('employer_id', $userId),
            default => $this->model->query(),
        };

        $orders = $query
            ->filterByStatus($data)
            ->with(['freelancer:id,username', 'employer:id,username', 'orderItems', 'comments'])
            ->latest()
            ->simplePaginate(15);
        return $orders;
    }
    public function getOrderFinalFiles(Order $order)
    {
        return $this->showWithRelations($order, ['finalFiles']);
    }

    // public function getOrderItems(Order $order)
    // {
    //     $orderItems = OrderItem::where('order_id', $order->id)
    //         ->with('milestone')->get();
    //     return $orderItems;
    // }

    // public function getUncompleteItems(Order $order)
    // {
    //     $orderItems = OrderItem::where('order_id', $order->id)->where('status', 2)->first();
    //     return $orderItems;
    // }

    public function getUserCompletedOrders(User $targetUser = null): Paginator
    {
        $user = auth()->user();
        $user->active_role === 'admin' ? $userId = $targetUser->id : $userId = $user->id;

        $query = match (auth()->user()->active_role) {
            'freelancer' => $this->model->where('freelancer_id', $userId),
            'employer' => $this->model->where('employer_id', $userId),
        };

        $orders = $query->where('status', 3)
            ->with('freelancer:id,username', 'employer:id,username', 'orderItems')
            ->orderBy('created_at', 'desc')->simplePaginate(20);
        return $orders;
    }

    public function findById(int $orderId)
    {
        return $this->model->find($orderId);
    }

    public function showOrder(Order $order): Order
    {
        return $this->showWithRelations($order, ['comments']);
    }


}