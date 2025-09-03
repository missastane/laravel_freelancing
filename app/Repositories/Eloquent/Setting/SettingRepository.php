<?php

namespace App\Repositories\Eloquent\Setting;

use App\Http\Resources\Setting\SettingResource;
use App\Models\Setting\Setting;
use App\Repositories\Contracts\Setting\SettingRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    use HasUpdateTrait;
    use HasShowTrait;
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

     public function first()
     {
        return $this->model->first();
     }
    public function firstOrCreate(array $attributes)
    {
        return $this->model->firstOrCreate($attributes);
    }

    public function showSetting(Setting $setting)
    {
       $setting= $this->showWithRelations($setting,['tags']);
       return new SettingResource($setting);
    }
}