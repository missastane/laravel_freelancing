<?php

namespace App\Http\Services\Province;

use App\Models\Locale\Province;
use App\Repositories\Contracts\Locale\ProvinceRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class ProvinceService
{
    public function __construct(protected ProvinceRepositoryInterface $provinceRepository)
    {
    }

    public function getProvinces(int $perPage = 15, string $search = null, string $message = null): Paginator
    {
        return $this->provinceRepository->all(
            perPage: $perPage,
            sort: "name",
            search: $search,
            searchScope: 'search'
        );
    }

    public function showProvince(Province $province): Province
    {
        return $this->provinceRepository->showWithRelations($province);
    }

    public function storeProvince(array $data): Province
    {
        return $this->provinceRepository->create($data);
    }

    public function updateProvince(Province $province, array $data)
    {
        $this->provinceRepository->update($province, $data);
        return $province;
    }

    public function deleteProvince(Province $province)
    {
        return $this->provinceRepository->delete($province);
    }
}