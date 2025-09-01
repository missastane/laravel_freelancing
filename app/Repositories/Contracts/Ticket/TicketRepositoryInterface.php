<?php

namespace App\Repositories\Contracts\Ticket;

use App\Models\Ticket\Ticket;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface TicketRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function getAllTickets(array $data): Paginator;
    public function getUserTickets(array $data): Paginator;
    public function showTicket(Ticket $ticket): Ticket;
}