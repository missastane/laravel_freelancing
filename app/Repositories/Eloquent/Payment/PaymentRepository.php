<?php

namespace App\Repositories\Eloquent\Payment;

use App\Http\Resources\Payment\PaymentResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Payment\Payment;
use App\Repositories\Contracts\Payment\PaymentRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;

    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function getAllPayments(string $status)
    {
        $payments = $this->model->filterByStatus($status)
        ->with('user:id,first_name,last_name,national_code')
        ->orderBy('created_at','desc')->paginate(15);
        return new BaseCollection($payments, PaymentResource::class,null);
    }

    public function getByTransaction(string $authority)
    {
        return $this->model->where('transaction_id',$authority)->first();
    }

    public function showPayment(Payment $payment)
    {
        $result = $this->showWithRelations($payment,['user:id,first_name,last_name,national_code']);
        return new PaymentResource($result);
    }
}