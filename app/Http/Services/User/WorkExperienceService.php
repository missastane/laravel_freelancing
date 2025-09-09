<?php

namespace App\Http\Services\User;

use App\Models\Market\WorkExperience;
use App\Repositories\Contracts\User\WorkExperienceRepositoryInterface;

class WorkExperienceService
{
    public function __construct(protected WorkExperienceRepositoryInterface $workExperienceRepository){}

    public function getUserExperiences()
    {
        return $this->workExperienceRepository->getUserExperiences();
    }

    public function storeExperience(array $data)
    {
        $data['user_id'] = auth()->id();
        return $this->workExperienceRepository->create($data);
    }

    public function showExperience(WorkExperience $workExperience)
    {
        return $this->workExperienceRepository->showExperience($workExperience);
    }

    public function updateExperience(WorkExperience $workExperience, array $data)
    {
        return $this->workExperienceRepository->update($workExperience,$data);
    }

    public function deleteExperience(WorkExperience $workExperience)
    {
        return $this->workExperienceRepository->delete($workExperience);
    }
}