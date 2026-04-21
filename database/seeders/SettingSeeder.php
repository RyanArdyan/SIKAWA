<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'jam_masuk'], ['value' => '08:00']);
        Setting::updateOrCreate(['key' => 'jam_pulang'], ['value' => '16:00']);
    }
}
