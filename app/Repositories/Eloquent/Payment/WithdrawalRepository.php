<?php

namespace App\Repositories\Eloquent\Payment;

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

    public function getAllByFilter(array $data): Paginator
    {
        $Withdrawals = $this->model->filterByStatus($data)->
            with('freelancer:id,first_name,last_name', 'employer:id,first_name,last_name')->
            orderBy('created_at', 'desc')->simplePaginate(15);
        return $Withdrawals;
    }
    public function showRequest(Withdrawal $withdrawal)
    {
        return $this->showWithRelations($withdrawal, ['user:id,first_name,last_name']);
    }
}