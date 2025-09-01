<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\Paginator;

trait HasListTrait
{
    public function all(?array $relations = [], string $sort = 'created_at',string $dir="asc"):Paginator
    {
        if (empty($relations)) {
            return $this->model->orderBy($sort,$dir)->paginate(15);
        }
        return $this->model->with($relations)->orderBy($sort,$dir)->paginate(15);
    }
}