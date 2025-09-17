<?php

namespace App\Http\Services\DisputeRequest;

use App\Http\Services\Payment\WalletService;
use App\Models\Market\Conversation;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\User\DisputeRequest;
use App\Repositories\Contracts\Market\ConversationRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketRepositoryInterface;
use App\Repositories\Contracts\User\DisputeRequestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DisputePayoutService
{

    public function __construct(
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected WalletRepositoryInterface $walletRepository,
        protected WalletService $walletService,
        protected DisputeRequestRepositoryInterface $disputeRequestRepository,
        protected TicketRepositoryInterface $ticketRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected ConversationRepositoryInterface $conversationRepository
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
        if ($disputeRequest->disputeTicket) {
            $this->ticketRepository->update($disputeRequest->disputeTicket, ['status' => 3]);
        }
    }
    protected function changeOrderStatus(Order $order, int $status)
    {
        $this->orderRepository->update($order, ['status' => $status]);
    }
    protected function openConversation(DisputeRequest $disputeRequest)
    {
        $order = $disputeRequest->orderItem->order;
        $freelancerId = $order->freelancer_id;
        $employerId = $order->employer_id;
        $conversation = $this->conversationRepository->getConversationIfExists(
            $freelancerId,
            $employerId,
            Order::class,
            $order->id
        );
        return $this->conversationRepository->update($conversation,['status' => 1]);
    }
    protected function finalizeDispute(Order $order, DisputeRequest $disputeRequest)
    {
        $this->changeOrderStatus($order, 4);
        $this->approveDisputeRequest($disputeRequest);
        $this->closeDisputeTicket($disputeRequest);
    }
    protected function priceOfCancelOtherItems(Order $order, ?OrderItem $except = null): int
    {
        $amount = 0;
        $items = $this->orderItemRepository->getUnApprovedOrderItemsExecpetOne($order,$except);
        foreach ($items as $item) {
            $this->orderItemRepository->update($item, ['status' => 5]);
            $amount += $item->price;
            $this->walletService->createTransaction(
                $order->employer->wallet,
                $item->price,
                5,
                'بازگشت پول بابت لغو مرحله',
                OrderItem::class,
                $item->id
            );
        }
        return $amount;
    }
    protected function openOrderItemLock(DisputeRequest $disputeRequest)
    {
        $orderItem = $disputeRequest->orderItem;
        return $this->orderItemRepository->update($orderItem,[
            'status' => 2,
            'locked_by' => null,
            'locked_reason' => null,
            'locked_note' => null,
            'locked_at' => null,
            'delivered_at' => null
        ]);
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
        DB::transaction(function () use ($order, $orderItem, $employerWallet, $disputeRequest, $amount) {
            $amount = $this->priceOfCancelOtherItems($order, $orderItem);
            $this->walletRepository->decrementLocked($employerWallet, $amount + $orderItem->price);
            $this->finalizeDispute($order, $disputeRequest);
        });
        return 'مبلغ این مرحله از پروژه طبق رای ادمین به کیف پول کارفرما بازگردانده شد و باقی پروژه لغو شد';
    }
    public function payToFreelancer(DisputeRequest $disputeRequest)
    {
        [$orderItem, $order, $employerWallet, $freelancerWallet] = $this->extractCommonData($disputeRequest, true);
        $result = DB::transaction(function () use ($order, $orderItem, $employerWallet, $freelancerWallet, $disputeRequest) {
            $employerAmount = $this->priceOfCancelOtherItems($order, $orderItem);
            $this->walletRepository->decrementLocked($employerWallet, $employerAmount);
            $msg = 'پول بلوکه شده این مرحله از سفارش طبق رأی داور برای فریلنسر آزاد شد';
            $this->walletService->transferFromLockedToBalance(
                $employerWallet,
                $freelancerWallet,
                $orderItem->price,
                $orderItem->freelancer_amount,
                $msg,
                $orderItem->id
            );
            $this->orderItemRepository->update($orderItem, ['status' => 4]); //approved(by admin and paid the money)
            $this->finalizeDispute($order, $disputeRequest);
            return $msg;
        });
        return $result . ' و باقی پروژه لغو شد';
    }
    public function moneyDistribution(DisputeRequest $disputeRequest, int $freelancerPercent, int $employerPercent)
    {
        DB::transaction(function () use ($disputeRequest, $freelancerPercent, $employerPercent) {
            [$orderItem, $order, $employerWallet, $freelancerWallet] = $this->extractCommonData($disputeRequest, true);
            $employerAmount = $this->priceOfCancelOtherItems($order, $orderItem);
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
                $freelancerNetAmount + $siteFee,
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
        });
        return 'پول این مرحله از سفارش طبق رای ادمین پس از کسر کارمزد سایت میان فریلنسر و کارفرما تقسیم شد و باقی پروژه لغو شد';
    }
    public function noChange(DisputeRequest $disputeRequest)
    {
        DB::transaction(function () use ($disputeRequest) {
            $this->rejectDisputeRequest($disputeRequest);
            $this->closeDisputeTicket($disputeRequest);
            $this->openOrderItemLock($disputeRequest);
            $this->openConversation($disputeRequest);
        });
        return 'این مرحله از سفارش طبق رای ادمین همچنان جاری بوده و از حالت قفل شده خارج  شده و پروژه ادامه می یابد';
    }
}