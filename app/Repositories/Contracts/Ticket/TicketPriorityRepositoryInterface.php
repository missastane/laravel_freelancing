<?php

namespace App\Repositories\Contracts\Ticket;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface TicketPriorityRepositoryInterface extends BaseRepositoryInterface
{
    public function getPriorityOption(): Collection;
}
