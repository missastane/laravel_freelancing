<?php

namespace App\Http\Services\Rating;

use App\Exceptions\User\AlreadyRatedException;
use App\Models\Market\Order;
use App\Models\Market\Rating;
use App\Models\User\User;
use App\Repositories\Contracts\User\RatingRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class RatingService
{

    public function __construct(protected RatingRepositoryInterface $ratingRepository)
    {
    }

    public function getContextRates(string $context, int $contextId): Paginator
    {
        return $this->ratingRepository->getContextRates($context, $contextId);
    }
    public function addRate(array $data, string $context, int $contextId, ?int $orderId = null)
    {
        $alreadyRated = $this->ratingRepository->isAlreadyRated($context, $contextId, $orderId);
        if ($alreadyRated) {
            throw new AlreadyRatedException();
        }
        $rating = Rating::create([
            'rate_by' => auth()->id(),
            'ratable_type' => $context,
            'ratable_id' => $contextId,
            'value' => $data['value'],
            'order_id' => $orderId ? $orderId : null
        ]);
        return $rating;
    }

}