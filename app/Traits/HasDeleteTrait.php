<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasDeleteTrait
{
    public function delete(Model $model)
    {
        return $model->delete();
    }
}