<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\Paginator;

interface ListableRepositoryInterface
{
    public function all(?array $relations = [], string $sort = 'created_at',string $dir="asc"):Paginator;
}