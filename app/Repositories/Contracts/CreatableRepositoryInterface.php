<?php

namespace App\Repositories\Contracts;

interface CreatableRepositoryInterface
{
    public function create(array $data);
}