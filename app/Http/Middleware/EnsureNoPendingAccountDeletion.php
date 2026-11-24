<?php

namespace App\Http\Middleware;

use App\Models\User\AccountDeletionRequest;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNoPendingAccountDeletion
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $hasPendingDeletionRequest = $user->accountDeletionRequests()
            ->where('status', AccountDeletionRequest::STATUS_PENDING)
            ->exists();

        if ($hasPendingDeletionRequest) {
            return ApiResponse::error(
                __('errors.account_deletion.pending_request_restricted'),
                403,
                'ACCOUNT_DELETION_PENDING'
            );
        }

        return $next($request);
    }
}
