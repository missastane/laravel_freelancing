<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface UpdatableRepositoryInterface
{
    public function update(Model $model, array $data);
}