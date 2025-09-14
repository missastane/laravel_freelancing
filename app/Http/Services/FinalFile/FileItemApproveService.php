<?php

namespace App\Http\Services\FinalFile;

use App\Models\Market\FinalFile;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\User\User;
use App\Repositories\Contracts\Market\FinalFileRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Contracts\Market\ProjectRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FileItemApproveService
{
    protected User $user;
    public function __construct(
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected WalletRepositoryInterface $walletRepository,
        protected FinalFileRepositoryInterface $finalFileRepository,
        protected ProjectRepositoryInterface $projectRepository
    ) {
        $this->user = auth()->user();
    }

    protected function updateOrderItem(FinalFile $finalFile)
    {
        return $this->orderItemRepository->update($finalFile->orderItem, [
            'status' => 4
        ]);
    }

    protected function setNewInProgressItem(FinalFile $finalFile)
    {
        $orderItem = $this->orderItemRepository->getFirstPendingItem($finalFile->orderItem->order);
        if ($orderItem) {
            return $this->orderItemRepository->update($orderItem, ['status' => 2]);
        }
    }
    protected function createTransaction(int $walletId, int $amount, int $type, string $description, string $relatedType, int $orderItemId)
    {
        $this->walletTransactionRepository->create([
            'wallet_id' => $walletId,
            'amount' => $amount,
            'transaction_type' => $type,
            'description' => $description,
            'related_type' => $relatedType,
            'related_id' => $orderItemId
        ]);
    }
    protected function updateEmployerWallet(FinalFile $finalFile)
    {
        $amount = $finalFile->orderItem->price;
        $employerWallet = $this->walletRepository->findByUserId($finalFile->employer_id);
        $this->walletRepository->update($employerWallet, [
            'balance' => $employerWallet->balance - $amount,
            'locked_balance' => $employerWallet->locked_balance - $amount
        ]);
        $this->createTransaction($employerWallet->id, $amount, 4, 'مبلغ این مرحله از سفارش آزاد و به کیف پول فریلنسر منتقل شد', OrderItem::class, $finalFile->order_item_id);
    }
    protected function updateFreelancerWallet(FinalFile $finalFile)
    {
        $freelancerWallet = $this->walletRepository->findByUserId($finalFile->freelancer_id);
        // here we must calculate platform fee percent and de
        $freelancerAmount = $finalFile->orderItem->freelancer_amount;
        $this->walletRepository->update($freelancerWallet, [
            'balance' => $freelancerWallet->balance + $freelancerAmount
        ]);
        $this->createTransaction($freelancerWallet->id, $freelancerAmount, 1, 'کارفرما مبلغ این مرحله از سفارش را آزاد نموده است', OrderItem::class, $finalFile->order_item_id);
    }
    protected function updateStatusesToComplete(FinalFile $finalFile)
    {
        $order = $finalFile->orderItem->order;
        $allItemsApproved = $order->orderItems->every(fn($item) => $item->status == 4);
        if ($allItemsApproved) {
            $this->orderRepository->update($order, [
                'status' => 3, //complete
            ]);
            $this->updateProject($order);
        }
    }
    protected function updateProject(Order $order)
    {
        $project = $order->proposal->project;
        return $this->projectRepository->update($project, ['status' => 3]); //complete
    }
    public function approveFileItem(FinalFile $finalFile)
    {
        return DB::transaction(function () use ($finalFile) {
            $this->finalFileRepository->update($finalFile, [
                'status' => 2,
                'employer_id' => $this->user->id,
                'approved_at' => now()
            ]);
            $this->updateOrderItem($finalFile);
            $this->updateEmployerWallet($finalFile);
            $this->updateFreelancerWallet($finalFile);
            $this->updateStatusesToComplete($finalFile);
            $this->setNewInProgressItem($finalFile);
            return $finalFile;
        });
    }
}