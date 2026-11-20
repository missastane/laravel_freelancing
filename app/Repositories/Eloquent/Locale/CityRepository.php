<?php

namespace App\Repositories\Eloquent\Locale;

use App\Models\Locale\City;
use App\Models\Locale\Province;
use App\Repositories\Contracts\Locale\CityRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CityRepository extends BaseRepository implements CityRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;
    public function __construct(City $model)
    {
        parent::__construct($model);
    }

    public function getCities(
        Province $province,
        int $perPage = 15,
        null|string $search = null
    ): LengthAwarePaginator {
        return $this->model
            ->where('province_id', $province->id)
            ->search($search)
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    public function showCity(City $city)
    {
        return $this->showWithRelations($city);
    }

}