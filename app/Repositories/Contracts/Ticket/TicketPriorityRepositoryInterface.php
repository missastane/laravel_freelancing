<?php

namespace App\Repositories\Contracts\Ticket;

use App\Models\Ticket\TicketPriority;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface TicketPriorityRepositoryInterface extends BaseRepositoryInterface
{
    public function getPriorities();
    public function showPriority(TicketPriority $ticketPriority);
    public function getPriorityOption(): Collection;
}
