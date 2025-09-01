<?php

namespace App\Repositories\Contracts\Ticket;

use App\Models\Ticket\TicketMessage;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface TicketMessageRepositoryInterface extends
CreatableRepositoryInterface,
ShowableRepositoryInterface,
UpdatableRepositoryInterface,
DeletableRepositoryInterface
{
    public function showTicketMessage(TicketMessage $ticketMessage):TicketMessage;
}