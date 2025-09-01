<?php

namespace App\Repositories\Eloquent\Payment;

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

    public function getAllPayments(array $data): Paginator
    {
        $payments = Payment::filterByStatus($data)
        ->with('user:id,first_name,last_name')
        ->orderBy('created_at','desc')->simplePaginate(15);
        return $payments;
    }

    public function getByTransaction(int $authority): Payment
    {
        return Payment::where('transaction_id',$authority)->first();
    }

    public function showPayment(Payment $payment):Payment
    {
        return $this->showWithRelations($payment,['user:id,first_name,last_name']);
    }
}