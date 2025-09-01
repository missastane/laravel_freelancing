<?php

namespace App\Repositories\Eloquent\Ticket;

use App\Models\Ticket\TicketPriority;
use App\Repositories\Contracts\Ticket\TicketPriorityRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Database\Eloquent\Collection;

class TicketPriorityRepository extends BaseRepository implements TicketPriorityRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(TicketPriority $model)
    {
        parent::__construct($model);
    }
    public function getPriorityOption(): Collection
    {
        $priorities = $this->model->query()->select('id', 'name')->get();
        return $priorities;
    }

}