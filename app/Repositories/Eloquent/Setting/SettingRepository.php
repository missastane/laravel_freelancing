<?php

namespace App\Repositories\Eloquent\Setting;

use App\Models\Setting\Setting;
use App\Repositories\Contracts\Setting\SettingRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasUpdateTrait;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    use HasUpdateTrait;
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }
}