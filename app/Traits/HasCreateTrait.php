<?php

namespace App\Traits;

trait HasCreateTrait
{
    public function create(array $data)
    {
        return $this->model->create($data);
    }
}