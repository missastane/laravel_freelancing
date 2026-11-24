<?php

namespace App\Http\Services\User;

use App\Models\User\User;
use App\Models\User\AccountDeletionRequest;
use App\Models\User\DisputeRequest;
use App\Models\Market\Order;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User\AuthSession;
use App\Http\Services\Auth\RefreshTokenService;

class AccountDeletionService
{
    public function __construct(
        private RefreshTokenService $refreshTokenService
    ) {
    }

    public function requestDeletion(array $data): array
    {
        $user = auth()->user();

        return DB::transaction(function () use ($user, $data) {

            $user = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($user->isAdmin()) {
                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.admin_not_allowed'),
                    'code' => 403,
                ];
            }

            if (!Hash::check($data['password'], $user->password)) {
                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.invalid_password'),
                    'code' => 422,
                ];
            }

            $existingRequest = $user->accountDeletionRequests()
                ->where('status', AccountDeletionRequest::STATUS_PENDING)
                ->exists();

            if ($existingRequest) {
                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.pending_request_exists'),
                    'code' => 409,
                ];
            }

            $blockers = $this->getDeletionBlockers($user);

            if (!empty($blockers)) {
                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.blocked')
                        . "\n\n• " . implode("\n• ", $blockers),
                    'code' => 409,
                ];
            }

            $deletionRequest = $user->accountDeletionRequests()->create([
                'status' => AccountDeletionRequest::STATUS_PENDING,
                'reason' => $data['reason'] ?? null,
                'scheduled_for' => now()->addDays(7),
            ]);

            return [
                'status' => true,
                'message' => __('messages.account_deletion.request_created'),
                'data' => $deletionRequest,
                'code' => 201,
            ];
        });
    }

    protected function getDeletionBlockers(User $user): array
    {
        $blockers = [];

        // Active orders
        $hasActiveOrder = Order::query()
            ->where(function ($query) use ($user) {
                $query->where('employer_id', $user->id)
                    ->orWhere('freelancer_id', $user->id);
            })
            ->where('status', Order::STATUS_IN_PROGRESS)
            ->exists();

        if ($hasActiveOrder) {
            $blockers[] = __('errors.account_deletion.blockers.active_orders');
        }

        // Pending disputes
        $hasPendingDispute = DisputeRequest::query()
            ->where('status', DisputeRequest::STATUS_PENDING)
            ->whereHas('orderItem.order', function ($query) use ($user) {
                $query->where('employer_id', $user->id)
                    ->orWhere('freelancer_id', $user->id);
            })
            ->exists();

        if ($hasPendingDispute) {
            $blockers[] = __('errors.account_deletion.blockers.pending_disputes');
        }

        // Employer's open or active projects
        $hasActiveProject = Project::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                Project::STATUS_PENDING,
                Project::STATUS_IN_PROGRESS,
            ])
            ->exists();

        if ($hasActiveProject) {
            $blockers[] = __('errors.account_deletion.blockers.active_projects');
        }

        // Freelancer's pending proposals
        $hasPendingProposal = Proposal::query()
            ->where('freelancer_id', $user->id)
            ->where('status', Proposal::STATUS_PENDING)
            ->exists();

        if ($hasPendingProposal) {
            $blockers[] = __('errors.account_deletion.blockers.pending_proposals');
        }

        // Wallet balance and locked balance
        $hasOutstandingBalance = $user->wallet()
            ->where(function ($query) {
                $query->where('balance', '!=', 0)
                    ->orWhere('locked_balance', '!=', 0);
            })
            ->exists();

        if ($hasOutstandingBalance) {
            $blockers[] = __('errors.account_deletion.blockers.outstanding_balance');
        }

        // Pending payments
        $hasPendingPayment = Payment::query()
            ->where('user_id', $user->id)
            ->where('status', Payment::STATUS_PENDING)
            ->exists();

        if ($hasPendingPayment) {
            $blockers[] = __('errors.account_deletion.blockers.pending_payments');
        }

        return $blockers;
    }

    public function cancelDeletion(): array
    {
        $user = auth()->user();

        return DB::transaction(function () use ($user) {

            $user = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $deletionRequest = $user->accountDeletionRequests()
                ->where('status', AccountDeletionRequest::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (!$deletionRequest) {
                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.no_pending_request'),
                    'code' => 409,
                ];
            }

            if ($deletionRequest->scheduled_for->isPast()) {
                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.cancellation_period_expired'),
                    'code' => 409,
                ];
            }

            $deletionRequest->update([
                'status' => AccountDeletionRequest::STATUS_CANCELLED,
            ]);

            return [
                'status' => true,
                'message' => __('messages.account_deletion.request_cancelled'),
                'data' => $deletionRequest->fresh(),
                'code' => 200,
            ];
        });
    }

    public function processDeletionRequest(int $requestId): array
    {
        // Get the user ID before starting the transaction.
        $userId = AccountDeletionRequest::query()
            ->whereKey($requestId)
            ->value('user_id');

        if (!$userId) {
            return [
                'status' => false,
                'message' => __('errors.account_deletion.request_not_found'),
                'code' => 404,
            ];
        }

        return DB::transaction(function () use ($requestId, $userId) {

            // Always lock the user first.
            $user = User::query()
                ->whereKey($userId)
                ->lockForUpdate()
                ->first();

            if (!$user) {
                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.user_not_found'),
                    'code' => 404,
                ];
            }

            // Then lock the deletion request.
            $deletionRequest = $user->accountDeletionRequests()
                ->whereKey($requestId)
                ->lockForUpdate()
                ->first();

            if (
                !$deletionRequest ||
                $deletionRequest->status !== AccountDeletionRequest::STATUS_PENDING ||
                $deletionRequest->scheduled_for->isAfter(now())
            ) {
                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.request_not_eligible'),
                    'code' => 409,
                ];
            }

            // Re-check all deletion blockers.
            $blockers = $this->getDeletionBlockers($user);

            if (!empty($blockers)) {
                $deletionRequest->update([
                    'status' => AccountDeletionRequest::STATUS_FAILED,
                    'failure_reason' => implode("\n", $blockers),
                    'processed_at' => now(),
                ]);

                return [
                    'status' => false,
                    'message' => __('errors.account_deletion.blocked')
                        . "\n\n• " . implode("\n• ", $blockers),
                    'code' => 409,
                ];
            }

            // Revoke all active authentication sessions and their refresh tokens.
            $sessions = AuthSession::query()
                ->where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->get();

            foreach ($sessions as $session) {
                $this->refreshTokenService->revokeSession($session);
            }

            // Soft delete the user.
            $user->delete();

            // Mark the deletion request as completed.
            $deletionRequest->update([
                'status' => AccountDeletionRequest::STATUS_COMPLETED,
                'processed_at' => now(),
                'failure_reason' => null,
            ]);

            return [
                'status' => true,
                'data' => null,
                'message' => __('messages.account_deletion.completed'),
                'code' => 200,
            ];
        });
    }
}
