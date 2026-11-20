<?php

namespace App\Repositories\Contracts\Market;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface SkillRepositoryInterface extends BaseRepositoryInterface
{
    public function skillOption(): Collection;
}