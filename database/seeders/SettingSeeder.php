<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('setting')->insert([
            'title' => 'عنوان سایت',
            'description' => 'توضیحات سایت',
            'icon' => 'icon.png',
            'logo' => 'logo.png',

        ]);
    }
}
