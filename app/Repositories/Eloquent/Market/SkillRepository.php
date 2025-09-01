<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\Skill;
use App\Repositories\Contracts\Market\SkillRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class SkillRepository extends BaseRepository implements SkillRepositoryInterface
{
    use HasCRUDTrait;
    public function searchSkill(string $search): Paginator
    {
        $skills = $this->model->where('original_title', 'LIKE', "%" . $search . "%")
            ->orWhere('persian_title', 'LIKE', '%' . $search . '%')
            ->orderBy('persian_title')->simplePaginate(20);
        return $skills;
    }

    public function skillOption(): Collection
    {
        return $this->model->query()->select('id','persian_title','original_title')->get();
    }

}