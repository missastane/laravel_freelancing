<?php

namespace App\Http\Services\Setting;

use App\Http\Services\Public\MediaStorageService;
use App\Http\Services\Tag\TagStorageService;
use App\Repositories\Contracts\Setting\SettingRepositoryInterface;
use Database\Seeders\SettingSeeder;
use Illuminate\Support\Facades\DB;
class SettingService
{
    public function __construct(
        protected SettingRepositoryInterface $settingRepository,
        protected MediaStorageService $mediaStorageService,
        protected TagStorageService $tagStorageService
        )
    {
    }

    public function getSetting()
    {
        $setting = $this->settingRepository->first();
        if ($setting === null) {
            $default = new SettingSeeder();
            $default->run();
            $setting = $this->settingRepository->first();
        }
        return $this->settingRepository->showSetting($setting);
    }

    public function update(array $data)
    {
        return DB::transaction(function () use($data){
            $setting = $this->settingRepository->first();
            $data['icon'] = $this->mediaStorageService->updateImageIfExists(
                $data['icon'],
                $setting->icon,
                'images/setting',
                'icon'
            );
            $data['logo'] = $this->mediaStorageService->updateImageIfExists(
                $data['logo'],
                $setting->logo,
                'images/setting',
                'logo'
            );
            $this->settingRepository->update($setting,$data);
            $this->tagStorageService->syncTags($setting,$data['keywords']);
            return $setting;
        });
    }
}