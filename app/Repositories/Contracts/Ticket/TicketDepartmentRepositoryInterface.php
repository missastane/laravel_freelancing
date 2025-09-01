<?php

namespace App\Repositories\Contracts\Ticket;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface TicketDepartmentRepositoryInterface extends BaseRepositoryInterface
{
    public function getDepartmentOption(): Collection;
}