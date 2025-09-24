<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\ArbitrationRequestResource;
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

    public function getAllByFilter(?string $status = null)
    {
        $requests = $this->model->filterByStatus($status)
        ->orderBy('created_at','desc')->paginate(15);
        return new BaseCollection($requests,ArbitrationRequestResource::class,null);
    }

    public function showArbitrationRequest(ArbitrationRequest $arbitrationRequest)
    {
        $result = $this->showWithRelations($arbitrationRequest,['admin:id,first_name,last_name']);
        return new ArbitrationRequestResource($result);
    }

}