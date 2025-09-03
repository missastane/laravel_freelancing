<?php

namespace App\Repositories\Contracts\Setting;

use App\Models\Setting\Setting;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface SettingRepositoryInterface extends UpdatableRepositoryInterface,ShowableRepositoryInterface
{
    public function first();
    public function firstOrCreate(array $attributes);
    public function showSetting(Setting $setting);
}