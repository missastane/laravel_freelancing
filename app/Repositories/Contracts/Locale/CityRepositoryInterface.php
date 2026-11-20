<?php

namespace App\Repositories\Contracts\Locale;

use App\Models\Locale\City;
use App\Models\Locale\Province;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CityRepositoryInterface extends
    ShowableRepositoryInterface,
    CreatableRepositoryInterface,
    UpdatableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function getCities(
        Province $province,
        int $perPage = 15,
        null|string $search = null
    ): LengthAwarePaginator;
    public function showCity(City $city);
}
