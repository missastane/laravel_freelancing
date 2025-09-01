<?php

namespace App\Http\Services\Payment;

use App\Models\Payment\Payment;
use App\Repositories\Contracts\Payment\PaymentRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Log;

class PaymentService
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected ZarinPalService $zarinPalService,
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected WalletService $walletService
    ) {
    }

    public function getPayments(array $data): Paginator
    {
        return $this->paymentRepository->getAllPayments($data);
    }

    public function showPayment(Payment $payment): Payment
    {
        return $this->paymentRepository->showPayment($payment);
    }

    protected function firstResponseSuccess(Payment $payment, array $result)
    {
        $this->paymentRepository->update($payment, [
            'transaction_id' => $result['authority'],
            'bank_first_response' => json_encode($result)
        ]);
        // this url returns https://www.zarinpal.com/pg/StartPay/S000000000000000000000000000000gmwrm
        // to text this url must remove wwww and replace it with sandbox -> go to zarinpal system
        // and get callback url and enter in postman with bearer auth token to get response ok
        return [
            'status' => true,
            'data' => ['payment_url' => $result['payment_url']],
            'message' => 'پرداخت با موفقیت انجام شد'
        ];
    }
    protected function firstResponseFailed(Payment $payment, array $result)
    {
        // fail zarinpal connection
        $this->paymentRepository->update($payment, [
            'status' => 3,
            'bank_first_response' => json_encode($result)
        ]);

        return [
            'status' => false,
            'message' => $result['message']
        ];
    }
    public function store(array $data)
    {
        $user = auth()->user();
        return DB::transaction(function () use ($data, $user) {
            $payment = $this->paymentRepository->create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'gateway' => 'zarinpal',
                'description' => $data['description']
            ]);
            $result = $this->zarinPalService->request(
                $payment->amount,
                $payment->description,
                $user->email ?? null,
                $user->mobile ?? null
            );
            return $result['success']
                ? $this->firstResponseSuccess($payment, $result)
                : $this->firstResponseFailed($payment, $result);
        });

    }

    public function verify(array $data)
    {
        return DB::transaction(function () use ($data) {
            $authority = $data['authority'];
            $status = $data['status'];
            $payment = $this->paymentRepository->getByTransaction($authority);
            if (!$payment) {
                 return [
                    'status' => false,
                    'message' => 'تراکنش یافت نشد',
                    'code' => 404
                ];
            }
            if ($status !== 'OK') {
                Log::info('status: not ok');
                $this->paymentRepository->update($payment, [
                    'status' => 3,
                    'bank_second_response' => json_encode(['status' => $status]),
                ]);
                return [
                    'status' => false,
                    'message' => 'پرداخت توسط کاربر لغو شد',
                    'code' => 422
                ];
            }
            $result = $this->zarinPalService->verify($authority, $payment->amount);
            if (!$result['success']) {
                Log::info('result: not success');
                $this->paymentRepository->update($payment, [
                    'status' => 3,
                    'bank_second_response' => json_encode($result),
                ]);
                return [
                    'status' => false,
                    'message' => 'تأیید پرداخت ناموفق بود',
                    'code' => 401
                ];
            }
            $this->paymentRepository->update($payment, [
                'status' => 2,
                'reference_id' => $result['ref_id'],
                'bank_second_response' => json_encode($result),
                'paid_at' => now()
            ]);
            $this->walletTransactionRepository->create([
                'wallet_id' => $payment->user->wallet->id,
                'transaction_type' => 1,
                'amount' => $payment->amount,
                'description' => 'شارژ کیف پول با پرداخت اینترنتی',
                'related_type' => Payment::class,
                'related_id' => $payment->id
            ]);
            $this->walletService->increment($payment->user->wallet, $payment->amount);
            return [
                'status' => true,
                'message' => 'تایید پرداخت با موفقیت انجام شد',
                'data' => [
                    'ref_id' => $result['ref_id'],
                    'amount' => $payment->amount
                ]
            ];
        });
    }

}