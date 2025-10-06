<?php

namespace App\Http\Services\Ticket;

use App\Http\Services\FileManagemant\FileManagementService;
use App\Http\Services\Public\MediaStorageService;
use App\Models\Market\File;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        protected TicketRepositoryInterface $ticketRepository,
        protected MediaStorageService $mediaStorageService,
        protected TicketMessageRepositoryInterface $ticketMessageRepository,
        protected TicketPriorityRepositoryInterface $ticketPriorityRepository,
        protected TicketDepartmentRepositoryInterface $ticketDepartmentRepository,
        protected FileManagementService $fileManagementService
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
        $user = auth()->user();
        $cacheKey = "user{$user->id}_tickets";
        return Cache::rememberForever($cacheKey, fn() => $this->ticketRepository->getUserTickets($status));
    }
    public function newTicketStore(array $data, int $authorType)
    {
        $user = auth()->user();
        $result = DB::transaction(function () use ($data, $authorType, $user) {
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
        if ($result) {
            Cache::forget("user{$user->id}_tickets");
        }
        return $result;
    }

    public function replyToTicket(TicketMessage $ticketMessage, array $data, int $authorType)
    {
        $user = auth()->user();
        $result = DB::transaction(function () use ($ticketMessage, $data, $authorType, $user) {
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
        if ($result) {
            Cache::forget("user{$ticketMessage->ticket->user_id}_tickets");
        }
        return $result;
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

        $result = DB::transaction(function () use ($ticketMessage, $data) {
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
        if ($result) {
            Cache::forget("user{$ticketMessage->ticket->user_id}_tickets");
        }
        return $result;
    }
    public function closeTicket(Ticket $ticket)
    {
        if ($ticket->status !== 3) {
            $result = $this->ticketRepository->update($ticket, ['status' => 3]);
            if ($result) {
                Cache::forget("user{$ticket->user_id}_tickets");
            }
            return true;
        } else {
            return false;
        }
    }

    public function deleteTicketFile(File $file)
    {
        // If a user's ticket list contains attachments to a message, the user's ticket cache should be cleared.
        return $this->fileManagementService->deleteFile($file);
    }
    public function deleteTicket(Ticket $ticket)
    {
        $result = $this->ticketRepository->delete($ticket);
        if ($result) {
            Cache::forget("user{$ticket->user_id}_tickets");
        }
        return $result;
    }

}