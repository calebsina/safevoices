<?php

namespace Database\Seeders;

use App\Models\Office\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $office = Office::updateOrCreate(['key' => 'yaounde-central'], ['region' => 'Centre', 'is_active' => true]);
        $office->syncTranslations([
            'en' => ['name' => 'Yaoundé Central Unit'],
            'fr' => ['name' => 'Unité centrale de Yaoundé'],
        ]);
    }
}
