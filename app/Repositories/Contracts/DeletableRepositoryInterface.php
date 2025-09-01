<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface DeletableRepositoryInterface
{
    public function delete(Model $model);
}