<?php

namespace App\Repositories\Contracts\Payment;

use App\Models\Payment\Payment;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface PaymentRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface
{
    public function getAllPayments(string $status);
    public function showPayment(Payment $payment);
    public function getByTransaction(string $authority);
}