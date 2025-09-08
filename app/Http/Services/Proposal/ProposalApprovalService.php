<?php

namespace App\Http\Services\Proposal;

use App\Exceptions\Market\NotEnoughBalanceException;
use App\Exceptions\Market\WalletLockException;
use App\Http\Services\Chat\ChatService;
use App\Models\Market\Order;
use App\Models\Market\Proposal;
use App\Models\Payment\Wallet;
use App\Models\User\User;
use App\Notifications\ApproveProposalNotification;
use App\Repositories\Contracts\Market\ConversationRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Contracts\Market\ProposalRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProposalApprovalService
{
    protected User $client;
    protected Wallet $wallet;

    public function __construct(
        protected ChatService $chatService,
        protected ConversationRepositoryInterface $conversationRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected WalletRepositoryInterface $walletRepository,
        protected ProposalRepositoryInterface $proposalRepository
    ) {
        $this->client = auth()->user();
        $this->wallet = $this->client->wallet;
        $this->chatService = $chatService;
    }

    public function approveProposal(Proposal $proposal)
    {
        $updatedProposal = DB::transaction(function () use ($proposal) {
            $this->validateWallet($proposal);
            $this->lockFunds($proposal);
            $this->updateProposals($proposal);

            $order = $this->createOrder($proposal);
            $this->createOrderItems($order, $proposal);
            $this->ensureConversation($order);
            $this->logTransaction($order, $proposal);
            return $proposal;
        });
        $freelancer = $proposal->freelancer;
        $freelancer->notify(new ApproveProposalNotification($updatedProposal, "کارفرما پیشنهاد شما را برای پروژه {$proposal->project->title} پذیرفت"));
        return $updatedProposal;
    }

    protected function validateWallet(Proposal $proposal)
    {
        if (!$this->walletRepository->hasEnoughBalance($this->client->id, $proposal->total_amount)) {
            throw new NotEnoughBalanceException();
        }
    }

    protected function lockFunds(Proposal $proposal)
    {
        try {
            $this->walletRepository->update($this->wallet, [
                'locked_balance' => $this->wallet->locked_balance + $proposal->total_amount
            ]);
        } catch (Throwable $e) {
            Log::error('خطا در بلوکه کردن پول: ' . $e->getMessage());
            throw new WalletLockException("خطا در بلوکه کردن پول", 500);
        }
    }

    protected function updateProposals(Proposal $proposal)
    {
        $this->proposalRepository->update($proposal, ['status' => 2]);
        $this->proposalRepository->updateWhere(
            [
                ['project_id', '=', $proposal->project_id],
                ['id', '!=', $proposal->id]
            ],
            ['status' => 3]
        );

    }

    protected function createOrder(Proposal $proposal): Order
    {
        return $this->orderRepository->create([
            'proposal_id' => $proposal->id,
            'freelancer_id' => $proposal->freelancer_id,
            'employer_id' => $this->client->id,
            'project_id' => $proposal->project_id,
            'total_price' => $proposal->total_amount,
            'due_date' => $proposal->due_date
        ]);
    }

    protected function createOrderItems(Order $order, Proposal $proposal)
    {
        $platformFeePercent = $proposal->freelancer->activeSubscription()
            ? $proposal->freelancer->activeSubscription->subscription->commission_rate
            : 10; // درصد پیش‌فرض

        foreach ($proposal->milestones as $milestone) {
            $platformFee = ($milestone->amount * $platformFeePercent) / 100;
            $freelancerAmount = $milestone->amount - $platformFee;
            \Log::info($milestone->due_date);
            $this->orderItemRepository->create([
                'order_id' => $order->id,
                'proposal_milestone_id' => $milestone->id,
                'price' => $milestone->amount,
                'platform_fee' => $platformFee,
                'freelancer_amount' => $freelancerAmount,
                'due_date' => $milestone->due_date,
            ]);
        }
    }


    protected function ensureConversation(Order $order)
    {
        $conversation = $this->conversationRepository->getConversationIfExists($order->freelancer_id, $order->employer_id);
        if (!$conversation) {
            $this->chatService->createConversation($order->freelancer, $order);
        }
    }

    protected function logTransaction(Order $order, Proposal $proposal)
    {
        try {
            $this->walletTransactionRepository->create([
                'wallet_id' => $this->wallet->id,
                'amount' => $proposal->total_amount,
                'transaction_type' => 3, // Blocked amount
                'description' => 'مبلغ سفارش به حالت بلوکه درآمد',
                'related_type' => Order::class,
                'related_id' => $order->id
            ]);
        } catch (Throwable $e) {
            Log::error('خطا در ثبت تراکنش: ' . $e->getMessage());
            throw new Exception('خطا در ثبت تراکنش', 500);
        }
    }
}
