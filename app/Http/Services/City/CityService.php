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

    public function getCities(Province $province, int $perPage = 15, string $search = null, string $message = null)
    {
        return $this->cityRepository->getCities($province, $perPage, $search, $message);
    }

    public function searchCity(string $search)
    {
        return $this->cityRepository->searchCity($search);
    }

    public function showCity(City $city)
    {
        return $this->cityRepository->showCity($city);
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