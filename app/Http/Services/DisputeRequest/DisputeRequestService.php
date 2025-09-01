<?php

namespace App\Http\Services\DisputeRequest;

use App\Models\User\DisputeRequest;
use App\Notifications\AddDisputeTicketNotification;
use App\Repositories\Contracts\Ticket\TicketRepositoryInterface;
use App\Repositories\Contracts\User\DisputeRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Notification;

class DisputeRequestService
{
    public function __construct(
        protected DisputeRequestRepositoryInterface $disputeRequestRepository,
        protected TicketRepositoryInterface $ticketRepository,
        protected DisputeJudgementService $disputeJudgementService
    ) {
    }

    public function getDisputeRequests(array $data)
    {
        return $this->disputeRequestRepository->getAllByFilter($data);
    }

    public function getUserRequests(): Paginator
    {
        return $this->disputeRequestRepository->getUserRequests();
    }

    public function createDisputeTicket(DisputeRequest $disputeRequest, array $data)
    {
        $orderItem = $disputeRequest->orderItem;
        $title = $orderItem->milstone->title;
        $orderId = $orderItem->order_id;
        $ticket = $this->ticketRepository->create([
            'user_id' => auth()->user()->id,
            'priority_id' => $data['priority_id'],
            'department_id' => $data['department_id'],
            'dispute_request_id' => $disputeRequest->id,
            'ticket_type' => 4, //complain
            'subject' => "تیکت داوری مرحله {$title} سفارش {$orderId}",
        ]);
        $users = [];
        $freelancer = $orderItem->order->freelancer;
        $employer = $orderItem->order->employer;
        array_push($users,[$freelancer,$employer]);
        Notification::send($users,new AddDisputeTicketNotification($orderItem->order->project));
    }
    public function showDisputeRequest(DisputeRequest $disputeRequest)
    {
        $request = $this->disputeRequestRepository->showDisputRequest($disputeRequest);
        if (auth()->user()->active_role !== 'admin') {
            return [
                'data' => $request,
                'message' => null
            ];
        } else {
            return [
                'data' => $request,
                'message' => 'جهت اطلاع از کم و کیف اختلاف نظر و گفتگو با طرفین لطفا یک تیکت داوری ایجاد کنید'
            ];
        }
    }

    public function judgeDisputeRequest(DisputeRequest $disputeRequest, array $data)
    {
        $this->disputeJudgementService->judgeRequest($disputeRequest, $data);
    }

    public function deleteDisputeRequest(DisputeRequest $disputeRequest)
    {
        return $this->disputeRequestRepository->delete($disputeRequest);
    }

}