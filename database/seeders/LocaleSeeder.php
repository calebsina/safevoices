<?php

namespace Database\Seeders;

use App\Models\Localization\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        Locale::updateOrCreate(['code' => 'en'], ['name' => 'English', 'is_default' => true, 'is_active' => true, 'sort_order' => 1]);
        Locale::updateOrCreate(['code' => 'fr'], ['name' => 'Français', 'is_default' => false, 'is_active' => true, 'sort_order' => 2]);
    }
}
