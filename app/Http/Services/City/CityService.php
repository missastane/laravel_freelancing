<?php

namespace App\Http\Services\City;

use App\Models\Locale\City;
use App\Models\Locale\Province;
use App\Repositories\Contracts\Locale\CityRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class CityService
{
    public function __construct(protected CityRepositoryInterface $cityRepository)
    {
    }

    public function getCities(Province $province)
    {
        return $this->cityRepository->showWithRelations($province, ['cities']);
    }

    public function searchCity(Province $province, string $search): Paginator
    {
        return $this->cityRepository->searchCity($province, $search);
    }

    public function showCity(City $city): City
    {
        return $this->cityRepository->showWithRelations($city, ['province:id,name']);
    }

    public function storeCity(array $data): City
    {
        return $this->cityRepository->create($data);
    }

    public function updateCity(City $city, array $data)
    {
        return $this->cityRepository->update($city, $data);
    }

    public function deleteCity(City $city)
    {
        return $this->cityRepository->delete($city);
    }
}