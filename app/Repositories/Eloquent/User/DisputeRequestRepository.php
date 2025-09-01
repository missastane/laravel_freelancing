<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User\DisputeRequest;
use App\Repositories\Contracts\User\DisputeRequestRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class DisputeRequestRepository extends BaseRepository implements DisputeRequestRepositoryInterface
{
    use HasUpdateTrait;
    use HasCreateTrait;
    use HasShowTrait;
    use HasDeleteTrait;
    public function __construct(DisputeRequest $model)
    {
        parent::__construct($model);
    }

    public function getAllByFilter(array $data): Paginator
    {
        $requests = $this->model->filterByStatus($data)
            ->with('orderItem', 'admin')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(15);
        return $requests;
    }
    public function getUserRequests(): Paginator
    {
        $requests = $this->model->where('raised_by',auth()->id())
            ->with('orderItem', 'admin')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(15);
        return $requests;
    }

    public function showDisputRequest(DisputeRequest $disputeRequest)
    {
        return $this->showWithRelations($disputeRequest,['orderItem','admin:id,username,active_role']);
    }
}