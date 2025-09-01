<?php

namespace App\Repositories\Contracts\Market;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface SkillRepositoryInterface extends BaseRepositoryInterface
{
    public function searchSkill(string $search): Paginator;
    public function skillOption(): Collection;
}