<?php

namespace App\Http\Services\User;

use App\Models\Market\UserEducation;
use App\Repositories\Contracts\Locale\ProvinceRepositoryInterface;
use App\Repositories\Contracts\Market\UserEducationRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class UserEducationService
{
    public function __construct(
        protected UserEducationRepositoryInterface $userEducationRepository,
        protected ProvinceRepositoryInterface $provinceRepository
        )
    {
    }

    public function getUserEducations(): Paginator
    {
        return $this->userEducationRepository->getUserEducations();
    }

    public function options()
    {
        return $this->provinceRepository->provinceOption();
    }

    public function storeEducation(array $data)
    {
        $data['user_id'] = auth()->id();
        return $this->userEducationRepository->create($data);
    }

    public function showEducation(UserEducation $userEducation)
    {
        return $this->userEducationRepository->showEducation($userEducation);
    }

    public function updateEducation(UserEducation $userEducation, array $data)
    {
        return $this->userEducationRepository->update($userEducation,$data);
    }

    public function deleteEducation(UserEducation $userEducation)
    {
        return $this->userEducationRepository->delete($userEducation);
    }
}