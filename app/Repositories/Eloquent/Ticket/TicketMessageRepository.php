<?php

namespace App\Repositories\Eloquent\Ticket;

use App\Http\Resources\Ticket\TicketMessageResource;
use App\Models\Ticket\TicketMessage;
use App\Repositories\Contracts\Ticket\TicketMessageRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class TicketMessageRepository extends BaseRepository implements TicketMessageRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;
    public function __construct(TicketMessage $model)
    {
        parent::__construct($model);
    }

    public function showTicketMessage(TicketMessage $ticketMessage)
    {
        $result = $this->showWithRelations($ticketMessage);
        return new TicketMessageResource($result);
    }

}