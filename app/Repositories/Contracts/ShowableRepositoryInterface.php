<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ShowableRepositoryInterface
{
    public function showWithRelations(Model $model, ?array $relations = []);
}
