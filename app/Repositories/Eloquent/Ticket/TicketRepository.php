<?php

namespace App\Repositories\Eloquent\Ticket;

use App\Models\Ticket\Ticket;
use App\Repositories\Contracts\Ticket\TicketRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class TicketRepository extends BaseRepository implements TicketRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;

    public function __construct(Ticket $model)
    {
        parent::__construct($model);
    }

    public function getAllTickets(array $data): Paginator
    {
        $tickets = $this->model->query()->filterByStatus($data)
            ->orderBy('created_at', 'desc')->simplePaginate(15);
        return $tickets;
    }

    public function getUserTickets(array $data): Paginator
    {
        $tickets = $this->model->where('user_id', auth()->id())
            ->filterByStatus($data)->orderBy('created_at', 'desc')
            ->simplePaginate(15);
        return $tickets;
    }

    public function showTicket(Ticket $ticket): Ticket
    {
        return $this->showWithRelations($ticket,['ticketMessages']);
    }



}