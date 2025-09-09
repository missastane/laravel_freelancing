<?php

namespace App\Repositories\Eloquent\Ticket;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\Ticket\TicketWithMessagesResource;
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

    public function getAllTickets(string $status)
    {
        $tickets = $this->model->query()->filterByStatus($status)->with('ticketMessages')
            ->orderBy('created_at', 'desc')->simplePaginate(15);
        return new BaseCollection($tickets, TicketWithMessagesResource::class, null);
    }

    public function getUserTickets(string $status)
    {
        $tickets = $this->model->where('user_id', auth()->id())
            ->filterByStatus($status)->with('ticketMessages')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return new BaseCollection($tickets, TicketWithMessagesResource::class, null);
    }

    public function showTicket(Ticket $ticket)
    {
        $result = $this->showWithRelations($ticket, ['ticketMessages']);
        return new TicketWithMessagesResource($result);
    }



}