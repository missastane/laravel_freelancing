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
    public function getAllPayments(array $data): Paginator;
    public function showPayment(Payment $payment): Payment;
    public function getByTransaction(int $authority): Payment;
}