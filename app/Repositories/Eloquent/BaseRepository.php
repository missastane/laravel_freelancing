<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
abstract class BaseRepository 
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // public function all()
    // {
    //     return $this->model->simplePaginate(15);
    // }

  
    // public function showWithRelations(Model $model, ?array $relations = [])
    // {
    //     if(!empty($relations)){{
    //         return $this->model;
    //     }}
    //     return $this->model->load($relations)->first();
    // }

    // public function create(array $data)
    // {
    //     return $this->model->create($data);
    // }

    // public function update($model, array $data)
    // {
    //     $model->update($data);
    //     return $model;
    // }

    // public function delete($model)
    // {
    //     return $model->delete();
    // }
}