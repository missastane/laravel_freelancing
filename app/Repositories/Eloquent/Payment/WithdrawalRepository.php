<?php

namespace App\Repositories\Eloquent\Payment;

use App\Http\Resources\Payment\WithDrawalRequestResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Payment\Withdrawal;
use App\Repositories\Contracts\Payment\WithdrawalRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class WithdrawalRepository extends BaseRepository implements WithdrawalRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    public function __construct(Withdrawal $model)
    {
        parent::__construct($model);
    }

    public function getAllByFilter(string $status)
    {
        $Withdrawals = $this->model->filterByStatus($status)->
            with('user:id,first_name,last_name,national_code')->
            orderBy('created_at', 'desc')->simplePaginate(15);
        return new BaseCollection($Withdrawals, WithDrawalRequestResource::class,null);
    }
    public function showRequest(Withdrawal $withdrawal)
    {
        $result = $this->showWithRelations($withdrawal, ['user:id,first_name,last_name,national_code']);
        return new WithDrawalRequestResource($result);
    }
}