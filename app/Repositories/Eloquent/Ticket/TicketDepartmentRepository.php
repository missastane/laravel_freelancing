<?php

namespace App\Repositories\Eloquent\Ticket;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\Ticket\TicketDepartmentResource;
use App\Models\Ticket\TicketDepartment;
use App\Repositories\Contracts\Ticket\TicketDepartmentRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Database\Eloquent\Collection;

class TicketDepartmentRepository extends BaseRepository implements TicketDepartmentRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(TicketDepartment $model)
    {
        parent::__construct($model);
    }

    public function getDepartments()
    {
        $departments = $this->all();
        return new BaseCollection($departments, TicketDepartmentResource::class,null);
    }

     public function showDepartment(TicketDepartment $ticketDepartment)
    {
        $ticketDepartment = $this->showWithRelations($ticketDepartment);
        return new TicketDepartmentResource($ticketDepartment);
    }
    public function getDepartmentOption(): Collection
    {
        $departments = $this->model->query()->select('id', 'name')->get();
        return $departments;
    }


}