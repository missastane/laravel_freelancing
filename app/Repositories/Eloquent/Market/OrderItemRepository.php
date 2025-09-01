<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\FinalFile;
use App\Models\Market\OrderItem;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    use HasUpdateTrait;

    public function __construct(OrderItem $model)
    {
        parent::__construct($model);
    }
    
}