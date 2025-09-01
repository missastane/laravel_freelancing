<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasShowTrait
{
     public function showWithRelations(Model $model, ?array $relations = [])
    {
        if(!empty($relations)){{
            return $model->load($relations);
        }}
        return $model;
    }
}