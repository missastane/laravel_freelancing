<?php

namespace App\Http\Services\User;

use App\Models\User\AccountDeletionRequest;
use App\Models\User\User;
use App\Support\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class AccountDeletionGuardService
{
    /**
     * Lock the user row and prevent commercial actions
     * while an account deletion request is pending.
     */
    public function lockUserForCommercialAction(int $userId): User
    {
        $user = User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->firstOrFail();

        $hasPendingDeletion = $user->accountDeletionRequests()
            ->where('status', AccountDeletionRequest::STATUS_PENDING)
            ->exists();

        if ($hasPendingDeletion) {
            throw new HttpResponseException(
                ApiResponse::error(
                    __('errors.account_deletion.pending_request_restricted'),
                    403,
                    'ACCOUNT_DELETION_PENDING'
                )
            );
        }

        return $user;
    }
}
