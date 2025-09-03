<?php

namespace App\Repositories\Contracts\Ticket;

use App\Models\Ticket\TicketDepartment;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface TicketDepartmentRepositoryInterface extends BaseRepositoryInterface
{
    public function getDepartments();
    public function showDepartment(TicketDepartment $ticketDepartment);
    public function getDepartmentOption(): Collection;
}