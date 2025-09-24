<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\RatingResource;
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

    public function getContextRates(string $context, int $contextId)
    {
        $rates = $this->model->where(['ratable_type' => $context, 'ratable_id' => $contextId])
            ->paginate(15);
        return new BaseCollection($rates, RatingResource::class,null);
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