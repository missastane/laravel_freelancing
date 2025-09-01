<?php

namespace App\Http\Services\Skill;

use App\Models\Market\Skill;
use App\Repositories\Contracts\Market\SkillRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class SkillService
{
    public function __construct(protected SkillRepositoryInterface $skillRepository)
    {
    }
    public function getSkills(): Paginator
    {
        return $this->skillRepository->all();
    }

    public function searchSkill(string $search): Paginator
    {
        return $this->skillRepository->searchSkill($search);
    }

    public function showSkill(Skill $skill): Skill
    {
        return $this->skillRepository->showWithRelations($skill);
    }

    public function storeSkill(array $data): Skill
    {
        return $this->skillRepository->create($data);
    }

    public function updateSkill(Skill $skill, array $data)
    {
        return $this->skillRepository->update($skill, $data);
    }

    public function deleteSkill(Skill $skill)
    {
        return $this->skillRepository->delete($skill);
    }
}