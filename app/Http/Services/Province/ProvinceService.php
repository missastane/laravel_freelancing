<?php

namespace App\Http\Services\Province;

use App\Models\Locale\Province;
use App\Repositories\Contracts\Locale\ProvinceRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class ProvinceService
{
    public function __construct(protected ProvinceRepositoryInterface $provinceRepository){}

    public function getProvinces():Paginator
    {
        return $this->provinceRepository->all(null);
    }

    public function searchProvince(string $search):Paginator
    {
        return $this->provinceRepository->searchProvince($search);
    }

    public function showProvince(Province $province):Province
    {
        return $this->provinceRepository->showWithRelations($province);
    }

    public function storeProvince(array $data):Province
    {
        return $this->provinceRepository->create($data);
    }

    public function updateProvince(Province $province, array $data)
    {
        return $this->provinceRepository->update($province,$data);
    }

    public function deleteProvince(Province $province)
    {
        return $this->provinceRepository->delete($province);
    }
}