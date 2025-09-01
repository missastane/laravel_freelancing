<?php

namespace App\Repositories\Eloquent\User;

use App\Models\Market\Rating;
use App\Repositories\Contracts\User\RatingRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class RatingRepository extends BaseRepository implements RatingRepositoryInterface
{
    use HasCreateTrait;
    public function __construct(Rating $model)
    {
        parent::__construct($model);
    }

    public function getContextRates(string $context, int $contextId): Paginator
    {
        $rates = $this->model->where(['ratable_type', 'ratable_id'], [$context, $contextId])
            ->makeHidden(['ratable_type', 'ratable_id'])
            ->simplePaginate(15);
        return $rates;
    }
    public function isAlreadyRated(string $context, int $contextId, ?int $orderId = null): bool
    {
        $query = $this->model->where('rate_by', auth()->id())->where('ratable_type', $context)
            ->where('ratable_id', $contextId);
        if ($orderId) {
            return $query->where('order_id', $orderId)
                ->exists();
        } else {
            return $query->exists();
        }
    }
}