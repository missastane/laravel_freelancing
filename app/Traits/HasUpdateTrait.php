<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasUpdateTrait
{
    public function update(Model $model,array $data)
    {
        return $model->update($data);
    }
}