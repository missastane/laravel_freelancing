<?php

namespace App\Http\Services\Ticket;

use App\Models\Ticket\TicketDepartment;
use App\Repositories\Contracts\Ticket\TicketDepartmentRepositoryInterface;

class TicketDepartmentService
{
    public function __construct(protected TicketDepartmentRepositoryInterface $ticketDepartmentRepository)
    {
    }

    public function getDepartments()
    {
        return $this->ticketDepartmentRepository->all();
    }

    public function showDepartment(TicketDepartment $ticketDepartment)
    {
        return $this->ticketDepartmentRepository->showWithRelations($ticketDepartment);
    }

    public function storeDepartment(array $data)
    {
        return $this->ticketDepartmentRepository->create($data);
    }

    public function updateDepartment(TicketDepartment $ticketDepartment, array $data)
    {
        return $this->ticketDepartmentRepository->update($ticketDepartment, $data);
    }

    public function changeStatus(TicketDepartment $ticketDepartment)
    {
        $ticketDepartment->status = $ticketDepartment->status === 1 ? 2 : 1;
        if ($ticketDepartment->save()) {
            $message = $ticketDepartment->status == 1 ?
                'دپارتمان تیکت با موفقیت فعال شد' :
                'دپارتمان تیکت با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }

    public function deleteDepartment(TicketDepartment $ticketDepartment)
    {
        return $this->ticketDepartmentRepository->delete($ticketDepartment);
    }
}