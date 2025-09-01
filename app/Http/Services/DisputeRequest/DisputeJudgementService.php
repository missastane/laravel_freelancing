<?php

namespace App\Http\Services\DisputeRequest;

use App\Http\Services\Payment\WalletService;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\User\ArbitrationRequest;
use App\Models\User\DisputeRequest;
use App\Models\User\User;
use App\Notifications\JudgeResultNotification;
use App\Repositories\Contracts\User\ArbitrationRequestRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DisputeJudgementService
{
    use ApiResponseTrait;
    public function __construct(
        protected DisputePayoutService $disputePayoutService,
        protected ArbitrationRequestRepositoryInterface $arbitrationRequestRepository
    ) {
    }

    public function judgeRequest(DisputeRequest $disputeRequest, array $data)
    {
       $message = DB::transaction(function () use ($disputeRequest, $data) {
            $result = $this->arbitrationRequestRepository->create([
                'dispute_request_id' => $disputeRequest->id,
                'status' => $data['status'],
                'freelancer_percent' => $data['freelancer_percent'],
                'employer_percent' => $data['employer_percent'],
                'description' => $data['description'],
                'resolved_by' => auth()->id(),
                'resolved_at' => now()
            ]);
            switch ($result->status) {
                case ArbitrationStatusService::EMPLOYER:
                    // to benifit of the employer
                    $msg = $this->disputePayoutService->payToEmployer($disputeRequest);
                    break;
                case ArbitrationStatusService::FREELANCER:
                    // to benifit of the freelancer
                    $msg = $this->disputePayoutService->payToFreelancer($disputeRequest);
                    break;
                case ArbitrationStatusService::DISTRIBUTED:
                    // money distribution
                    $msg = $this->disputePayoutService->moneyDistribution(
                        $disputeRequest,
                        $data['freelancer_percent'],
                        $data['employer_percent'
                        ]
                    );
                    break;
                case ArbitrationStatusService::NO_CHANGE:
                    // no change and continue the order
                    $msg = $this->disputePayoutService->noChange($disputeRequest);
                    break;
            }
            return $msg;
        });
        $orderItem = $disputeRequest->orderItem;
        $users = [];
        $freelancer = $orderItem->order->freelancer;
        $employer = $orderItem->order->employer;
        array_push($users, [$freelancer, $employer]);
        Notification::send($users, new JudgeResultNotification($message,$orderItem->order->project));
    }
}