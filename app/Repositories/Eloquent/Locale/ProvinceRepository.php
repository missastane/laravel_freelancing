<?php

namespace App\Repositories\Eloquent\Locale;

use App\Models\Locale\Province;
use App\Repositories\Contracts\Locale\ProvinceRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class ProvinceRepository extends BaseRepository implements ProvinceRepositoryInterface
{
    use HasCRUDTrait;
    public function __construct(Province $model)
    {
        parent::__construct($model);
    }
    public function searchProvince(string $search): Paginator
    {
        $result = $this->model->where('name', 'LIKE', "%{$search}%")
            ->orderBy('name', 'asc')->simplePaginate(15);
        return $result;
    }

    public function provinceOption(): Collection
    {
        return $this->model->query()->select('id','name')->orderBy('name')->get();
    }

   


}
