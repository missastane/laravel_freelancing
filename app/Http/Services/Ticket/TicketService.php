<?php

namespace App\Http\Services\Ticket;

use App\Http\Services\Public\MediaStorageService;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketDepartment;
use App\Models\Ticket\TicketMessage;
use App\Models\Ticket\TicketPriority;
use App\Repositories\Contracts\Ticket\TicketDepartmentRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketMessageRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketPriorityRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        protected TicketRepositoryInterface $ticketRepository,
        protected MediaStorageService $mediaStorageService,
        protected TicketMessageRepositoryInterface $ticketMessageRepository,
        protected TicketPriorityRepositoryInterface $ticketPriorityRepository,
        protected TicketDepartmentRepositoryInterface $ticketDepartmentRepository
    ) {
    }
    public function getAllTickets(string $status)
    {
        return $this->ticketRepository->getAllTickets($status);
    }

    public function options(): array
    {
        $departments = $this->ticketDepartmentRepository->getDepartmentOption();
        $priorities = $this->ticketPriorityRepository->getPriorityOption();
        return [
            'departments' => $departments,
            'priorities' => $priorities
        ];
    }
    public function getUserTickets(string $status)
    {
        return $this->ticketRepository->getUserTickets($status);
    }
    public function newTicketStore(array $data, int $authorType)
    {
        return DB::transaction(function () use ($data, $authorType) {
            $user = auth()->user();
            $ticket = $this->ticketRepository->create([
                'user_id' => $user->id,
                'priority_id' => $data['priority_id'],
                'department_id' => $data['department_id'],
                'ticket_type' => $data['ticket_type'],
                'subject' => $data['subject']
            ]);
            $ticketMessage = $this->ticketMessageRepository->create([
                'ticket_id' => $ticket->id,
                'author_id' => $user->id,
                'message' => $data['message'],
                'author_type' => $authorType
            ]);
            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                Ticketmessage::class,
                $ticketMessage->id,
                "files/ticket-messages/{$ticketMessage->id}",
                "private"
            );
            return $ticket;
        });

    }

    public function replyToTicket(TicketMessage $ticketMessage, array $data, int $authorType)
    {
        return DB::transaction(function () use ($ticketMessage, $data, $authorType) {
            $user = auth()->user();
            $answeredTicketMessage = $this->ticketMessageRepository->create([
                'ticket_id' => $ticketMessage->ticket_id,
                'author_id' => $user->id,
                'message' => $data['message'],
                'author_type' => $authorType,
                'parent_id' => $ticketMessage->id
            ]);
            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                TicketMessage::class,
                $answeredTicketMessage->id,
                "files/ticket-messages/{$answeredTicketMessage->id}",
                "private"
            );
            $this->ticketRepository->update($ticketMessage->ticket, ['status' => 2]);
            return $answeredTicketMessage;
        });


    }
    public function showTicket(Ticket $ticket)
    {
        return $this->ticketRepository->showTicket($ticket);
    }

    public function showTicketMessage(TicketMessage $ticketMessage)
    {
        return $this->ticketMessageRepository->showTicketMessage($ticketMessage);
    }
    public function updateTicketMessage(TicketMessage $ticketMessage, array $data)
    {
        return DB::transaction(function () use ($ticketMessage, $data) {
            $this->ticketMessageRepository->update($ticketMessage, ['message' => $data['message']]);
            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                TicketMessage::class,
                $ticketMessage->id,
                "files/ticket-messages/{$ticketMessage->id}",
                "private"
            );
            return $ticketMessage;
        });
    }
    public function closeTicket(Ticket $ticket)
    {
        if ($ticket->status !== 3) {
            $this->ticketRepository->update($ticket, ['status' => 3]);
            return true;
        } else {
            return false;
        }

    }
    public function deleteTicket(Ticket $ticket)
    {
        return $this->ticketRepository->delete($ticket);
    }

}