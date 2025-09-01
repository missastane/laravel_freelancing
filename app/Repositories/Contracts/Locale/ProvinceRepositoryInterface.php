<?php

namespace App\Repositories\Contracts\Locale;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface ProvinceRepositoryInterface extends BaseRepositoryInterface
{
    public function searchProvince(string $search): Paginator;
    public function provinceOption(): Collection;
}