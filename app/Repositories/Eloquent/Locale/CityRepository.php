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

    public function getCities(Province $province, int $perPage, null|string $search, null|string $message)
    {
        $result = $this->model->where('id', $province->id)
            ->with('province')
            ->search($search)
            ->orderBy('id', 'desc')->paginate($perPage);
        return new BaseCollection($result, CityResource::class, $message);
    }

    public function showCity(City $city)
    {
        return $this->showWithRelations($city, ['province:id,name']);
    }

}