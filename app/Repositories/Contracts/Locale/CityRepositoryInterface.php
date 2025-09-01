<?php

namespace App\Repositories\Contracts\Locale;

use App\Models\Locale\Province;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ListableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface CityRepositoryInterface extends 
ShowableRepositoryInterface,
CreatableRepositoryInterface,
UpdatableRepositoryInterface,
DeletableRepositoryInterface
{
    public function searchCity(Province $province, string $search): Paginator;
}
