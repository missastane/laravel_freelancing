<?php

namespace App\Http\Services\Ticket;

use App\Models\Ticket\TicketPriority;
use App\Repositories\Contracts\Ticket\TicketPriorityRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class TicketPriorityService
{
    public function __construct(protected TicketPriorityRepositoryInterface $ticketPriorityRepository){}

    public function getPriorities()
    {
        return $this->ticketPriorityRepository->getPriorities();
    }

    public function showPriority(TicketPriority $ticketPriority)
    {
        return $this->ticketPriorityRepository->showPriority($ticketPriority);
    }

    public function storePriority(array $data)
    {
        return $this->ticketPriorityRepository->create($data);
    }

    public function updatePriority(TicketPriority $ticketPriority, array $data)
    {
        return $this->ticketPriorityRepository->update($ticketPriority,$data);
    }

     public function changeStatus(TicketPriority $ticketPriority)
    {
        $ticketPriority->status = $ticketPriority->status === 1 ? 2 : 1;
        if ($ticketPriority->save()) {
            $message = $ticketPriority->status == 1 ?
                'اولویت تیکت با موفقیت فعال شد' :
                'اولویت تیکت با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }

    public function deletePriority(TicketPriority $ticketPriority)
    {
        return $this->ticketPriorityRepository->delete($ticketPriority);
    }
}