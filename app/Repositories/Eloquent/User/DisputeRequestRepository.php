<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\DisputeRequestResource;
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

    public function getAllByFilter(string $status)
    {
        $requests = $this->model->filterByStatus($status)
            ->with('orderItem', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return new BaseCollection($requests, DisputeRequestResource::class, null);
    }
    public function getUserRequests()
    {
        $requests = $this->model->where('raised_by', auth()->id())
            ->with('orderItem', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return new BaseCollection($requests, DisputeRequestResource::class, null);

    }

    public function showDisputRequest(DisputeRequest $disputeRequest)
    {
        return $this->showWithRelations($disputeRequest, ['orderItem', 'user:id,username,active_role']);
    }
}