<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User\ArbitrationRequest;
use App\Repositories\Contracts\User\ArbitrationRequestRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;

class ArbitrationRequestRepository extends BaseRepository implements ArbitrationRequestRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    public function __construct(ArbitrationRequest $model)
    {
        parent::__construct($model);
    }

    public function showArbitrationRequest(ArbitrationRequest $arbitrationRequest)
    {
        return $this->showWithRelations($arbitrationRequest,['admin:id,first_name,last_name']);
    }

}