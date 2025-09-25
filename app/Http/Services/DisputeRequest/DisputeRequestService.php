<?php

namespace App\Http\Services\DisputeRequest;

use App\Events\AddDisputeRequestEvent;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Models\User\DisputeRequest;
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Pagination\Paginator;
use App\Notifications\AddDisputeTicketNotification;
use App\Repositories\Contracts\Ticket\TicketRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Market\ConversationRepositoryInterface;
use App\Repositories\Contracts\User\DisputeRequestRepositoryInterface;

class DisputeRequestService
{
    public function __construct(
        protected DisputeRequestRepositoryInterface $disputeRequestRepository,
        protected TicketRepositoryInterface $ticketRepository,
        protected DisputeJudgementService $disputeJudgementService,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected ConversationRepositoryInterface $conversationRepository
    ) {
    }

    public function getDisputeRequests(string $status)
    {
        return $this->disputeRequestRepository->getAllByFilter($status);
    }

    public function getUserRequests()
    {
        return $this->disputeRequestRepository->getUserRequests();
    }

    public function createDisputeRequest(OrderItem $orderItem, array $data)
    {
        $user = auth()->user();
        $order = $orderItem->order;
        $result = DB::transaction(function () use ($orderItem, $order, $data, $user) {
            $disputeRequest = $this->disputeRequestRepository->create([
                'order_item_id' => $orderItem->id,
                'user_type' => $user->active_role === 'employer' ? 1 : 2,
                'raised_by' => $user->id,
                'reason' => $data['reason'],
            ]);
            $this->orderItemRepository->update($orderItem, [
                'locked_by' => $user->active_role === 'employer' ? 1 : 2,
                'locked_reason' => $data['locked_reason'],
                'locked_note' => $data['reason'],
                'locked_at' => now()
            ]);

            $conversation = $this->conversationRepository->getConversationIfExists(
                $order->freelancer_id,
                $order->employer_id,
                Order::class,
                $order->id
            );
            if ($conversation) {
                $this->conversationRepository->update($conversation, ['status' => 2]); //close
            }
            return $disputeRequest;
        });
        event(new AddDisputeRequestEvent($order->freelancer, $order->employer, $user, $orderItem->id));
        return $result;
    }
    public function createDisputeTicket(DisputeRequest $disputeRequest, array $data)
    {
        $orderItem = $disputeRequest->orderItem;
        $title = $orderItem->milestone->title;
        $orderId = $orderItem->order_id;
        $ticket = $this->ticketRepository->create([
            'user_id' => auth()->user()->id,
            'priority_id' => $data['priority_id'],
            'department_id' => $data['department_id'],
            'dispute_request_id' => $disputeRequest->id,
            'ticket_type' => 4, //complain
            'subject' => "تیکت داوری مرحله {$title} سفارش {$orderId}",
        ]);
        $freelancer = $orderItem->order->freelancer;
        $employer = $orderItem->order->employer;
        Notification::send([$employer, $freelancer], new AddDisputeTicketNotification($orderItem->order->project));
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

    public function withdrawn(DisputeRequest $disputeRequest)
    {
        $orderItem = $disputeRequest->orderItem;
        $order = $orderItem->order;
        $result = DB::transaction(function () use ($orderItem, $disputeRequest, $order) {
            $this->disputeRequestRepository->update($disputeRequest, ['status' => 3]); //withdrawn
            $this->orderItemRepository->update($orderItem, [
                'locked_by' => null,
                'locked_reason' => null,
                'locked_note' => null,
                'locked_at' => null
            ]);
            $conversation = $this->conversationRepository->getConversationIfExists(
                $order->freelancer_id,
                $order->employer_id,
                Order::class,
                $order->id
            );
            if ($conversation) {
                $this->conversationRepository->update($conversation, ['status' => 1]); //open
            }
            return $disputeRequest;
        });
        return $result;

    }

    public function deleteDisputeRequest(DisputeRequest $disputeRequest)
    {
        return $this->disputeRequestRepository->delete($disputeRequest);
    }

}