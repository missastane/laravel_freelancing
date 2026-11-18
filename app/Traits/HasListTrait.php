<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\Paginator;

trait HasListTrait
{
    public function all(
        ?array $relations = [],
        string $sort = 'created_at',
        string $dir = 'asc',
        ?string $search = null,
        int $perPage = 15,
        ?string $searchScope = null
    ): Paginator {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        if (!empty($search) && !empty($searchScope)) {
            $query->{$searchScope}($search);
        }

        return $query
            ->orderBy($sort, $dir)
            ->paginate($perPage);
    }
}