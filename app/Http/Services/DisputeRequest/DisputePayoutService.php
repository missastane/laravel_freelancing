<?php

namespace App\Http\Services\DisputeRequest;

use App\Http\Services\Payment\WalletService;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\User\DisputeRequest;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketRepositoryInterface;
use App\Repositories\Contracts\User\DisputeRequestRepositoryInterface;

class DisputePayoutService
{

    public function __construct(
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected WalletRepositoryInterface $walletRepository,
        protected WalletService $walletService,
        protected DisputeRequestRepositoryInterface $disputeRequestRepository,
        protected TicketRepositoryInterface $ticketRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository
    ) {
    }
    protected function approveDisputeRequest(DisputeRequest $disputeRequest)
    {
        $this->disputeRequestRepository->update($disputeRequest, ['status' => 2]);
    }
    protected function rejectDisputeRequest(DisputeRequest $disputeRequest)
    {
        $this->disputeRequestRepository->update($disputeRequest, ['status' => 3]);
    }
    protected function closeDisputeTicket(DisputeRequest $disputeRequest)
    {
        $this->ticketRepository->update($disputeRequest->disputeTicket, ['status' => 3]);
    }
    protected function changeOrderStatus(Order $order, int $status)
    {
        $this->orderRepository->update($order, ['status' => $status]);
    }
    protected function finalizeDispute(Order $order, DisputeRequest $disputeRequest)
    {
        $this->changeOrderStatus($order, 4);
        $this->approveDisputeRequest($disputeRequest);
        $this->closeDisputeTicket($disputeRequest);
    }
    protected function cancelOtherItems(Order $order, ?OrderItem $except = null): int
    {
        $amount = 0;
        $items = $order->orderItems()->where('status', '!=', 3);
        if ($except) {
            $items->where('id', '!=', $except->id);
        }
        $items = $items->get();
        foreach ($items as $item) {
            $this->orderItemRepository->update($item, ['status' => 6]);
            $amount += $item->price;
            $this->walletService->createTransaction(
                $order->employer->wallet->id,
                $item->price,
                5,
                'بازگشت پول بابت لغو مرحله',
                OrderItem::class,
                $item->id
            );
        }
        return $amount;
    }
    protected function extractCommonData(DisputeRequest $disputeRequest, bool $withFreelancer = false)
    {
        $orderItem = $disputeRequest->orderItem;
        $order = $orderItem->order;
        $employerWallet = $order->employer->wallet;

        if ($withFreelancer) {
            $freelancerWallet = $order->freelancer->wallet;
            return [$orderItem, $order, $employerWallet, $freelancerWallet];
        }

        return [$orderItem, $order, $employerWallet];
    }
    public function payToEmployer(DisputeRequest $disputeRequest)
    {
        [$orderItem, $order, $employerWallet] = $this->extractCommonData(
            $disputeRequest,
            false
        );
        $amount = 0;
        $amount = $this->cancelOtherItems($order, $orderItem);
        $this->walletRepository->decrementLocked($employerWallet, $amount);
        $this->finalizeDispute($order, $disputeRequest);
        return 'مبلغ این مرحله از پروژه طبق رای ادمین به کیف پول کارفرما بازگردانده شد و باقی پروژه لغو شد';
    }
    public function payToFreelancer(DisputeRequest $disputeRequest)
    {
        [$orderItem, $order, $employerWallet, $freelancerWallet] = $this->extractCommonData($disputeRequest, true);
        $employerAmount = $this->cancelOtherItems($order, $orderItem);
        $this->walletRepository->decrementLocked($employerWallet, $employerAmount);
        $msg = 'پول بلوکه شده این مرحله از سفارش طبق رأی داور برای فریلنسر آزاد شد';
        $this->walletService->transferFromLockedToBalance(
            $employerWallet,
            $freelancerWallet,
            $orderItem->freelancer_amount,
            $msg,
            $orderItem->id
        );
        $this->orderItemRepository->update($orderItem, ['status' => 3]); //completed
        $this->finalizeDispute($order, $disputeRequest);
        return $msg . ' و باقی پروژه لغو شد';
    }
    public function moneyDistribution(DisputeRequest $disputeRequest, int $freelancerPercent, int $employerPercent)
    {
        [$orderItem, $order, $employerWallet, $freelancerWallet] = $this->extractCommonData($disputeRequest, true);
        $employerAmount = 0;
        $employerAmount = $this->cancelOtherItems($order, $orderItem);
        $price = $orderItem->price;
        $siteFee = $orderItem->platform_fee; 
        $freelancerAmount = ($price * $freelancerPercent) / 100;
        $employerReturnedAmount = ($price * $employerPercent) / 100;
        $freelancerNetAmount = max(0, $freelancerAmount - $siteFee);
        $this->walletRepository->decrementLocked(
            $employerWallet,
            $employerAmount + $employerReturnedAmount
        );
        $employerDecreaseMsg = 'درصدی از پول بلوکه شده طبق رأی داور به کیف پول فریلنسر بازگردانده شد';
        $this->walletService->transferFromLockedToBalance(
            $employerWallet,
            $freelancerWallet,
            $freelancerNetAmount,
            $employerDecreaseMsg,
            $orderItem->id
        );
        $employerIncreaseMsg = 'درصدی از پول بلوکه شده طبق رأی داور به کیف پول کارفرما بازگردانده شد';
        $this->walletService->createTransaction(
            $employerWallet,
            $employerReturnedAmount,
            5,
            $employerIncreaseMsg,
            orderItem::class,
            $orderItem->id
        );
        $this->finalizeDispute($order, $disputeRequest);
        return 'پول این مرحله از سفارش طبق رای ادمین پس از کسر کارمزد سایت میان فریلنسر و کارفرما تقسیم شد و باقی پروژه لغو شد';
    }
    public function noChange(DisputeRequest $disputeRequest)
    {
        $this->rejectDisputeRequest($disputeRequest);
        $this->closeDisputeTicket($disputeRequest);
        return 'این مرحله از سفارش طبق رای ادمین همچنان جاری بوده و از حالت قفل شده خارج  شده و پروژه ادامه می یابد';
    }
}