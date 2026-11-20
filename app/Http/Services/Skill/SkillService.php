<?php

namespace App\Http\Services\Skill;

use App\Http\Resources\Market\SkillResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Market\Skill;
use App\Repositories\Contracts\Market\SkillRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class SkillService
{
    public function __construct(protected SkillRepositoryInterface $skillRepository)
    {
    }
    public function getSkills(int $perPage = 15, ?string $search)
    {
        $skills = $this->skillRepository->all(search: $search, perPage: $perPage, searchScope: 'search');
        return $skills;
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
        $this->skillRepository->update($skill, $data);
        return $skill;
    }

    public function deleteSkill(Skill $skill)
    {
        return $this->skillRepository->delete($skill);
    }
}