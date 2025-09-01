<?php

namespace App\Repositories\Eloquent\Locale;

use App\Http\Resources\Locale\CityResource;
use App\Http\Resources\Locale\ProvinceResource;
use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Models\Locale\City;
use App\Models\Locale\Province;
use App\Repositories\Contracts\Locale\CityRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class CityRepository extends BaseRepository implements CityRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;
    public function __construct(City $model)
    {
        parent::__construct($model);
    }

    public function getCities(Province $province)
    {
        $province = $this->showWithRelations($province, ['cities']);
        return new ProvinceResource($province);
    }
    public function searchCity(string $search)
    {
        $result = $this->model->where('name', 'LIKE', "%{$search}%")
            ->with('province')
            ->orderBy('name', 'asc')->paginate(15);
        return new BaseCollection($result, CityResource::class, null);
    }

    public function showCity(City $city)
    {
        return $this->showWithRelations($city, ['province:id,name']);
    }

}