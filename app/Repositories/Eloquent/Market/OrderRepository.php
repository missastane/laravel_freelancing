<?php

namespace App\Repositories\Eloquent\Market;

use App\Http\Resources\Market\OrderFinalFileResource;
use App\Http\Resources\Market\OrderItemsResource;
use App\Http\Resources\Market\OrderResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
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

    public function getAllOrders(string $status)
    {
        $orders = $this->model->filterByStatus($status)->with(['freelancer:id,first_name,last_name', 'employer:id,first_name,last_name', 'orderItems'])
            ->orderBy('created_at', 'desc')->simplePaginate(15);
        return new BaseCollection($orders, OrderResource::class, null);

    }

    public function getUserOrders(?User $user, ?string $status = null)
    {
        $currentUser = $user ?? auth()->user();
        $currentUserId = $user->id ?? auth()->id();

        $query = match ($currentUser->active_role) {
            'freelancer' => $this->model->where('freelancer_id', $currentUserId),
            'employer' => $this->model->where('employer_id', $currentUserId),
            default => $this->model->query(),
        };

        $orders = $query
            ->filterByStatus($status)
            ->with(['freelancer:id,username', 'employer:id,username', 'orderItems', 'comments'])
            ->latest()
            ->paginate(15);
        return new BaseCollection($orders, OrderResource::class, null);
    }
    public function getOrderFinalFiles(Order $order)
    {
        $result = $this->showWithRelations($order,['finalFiles']);
        return new OrderFinalFileResource($result);
    }

    public function getOrderItems(Order $order)
    {
        $result = $this->showWithRelations($order,['orderItems']);
        return new OrderItemsResource($result);
    }


    public function findById(int $orderId)
    {
        return $this->model->find($orderId);
    }

    public function showOrder(Order $order)
    {
        $result = $this->showWithRelations($order, ['freelancer:id,username','employer:id,username','orderItems','comments']);
        return new OrderResource($result);
    }


}