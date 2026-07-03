<?php

namespace Database\Seeders;

use App\Models\Setting\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Non-translatable config values.
        Setting::updateOrCreate(['key' => 'whatsapp.number'], [
            'group' => 'contact', 'type' => 'string', 'value' => '+237600000000', 'is_translatable' => false,
        ]);

        // Translatable public strings.
        $tagline = Setting::updateOrCreate(['key' => 'site.tagline'], [
            'group' => 'site', 'type' => 'string', 'is_translatable' => true,
        ]);
        $tagline->syncTranslations([
            'en' => ['value' => 'Speak safely. Be heard.'],
            'fr' => ['value' => 'Parlez en sécurité. Soyez entendu(e).'],
        ]);

        $notice = Setting::updateOrCreate(['key' => 'site.emergency_notice'], [
            'group' => 'site', 'type' => 'string', 'is_translatable' => true,
        ]);
        $notice->syncTranslations([
            'en' => ['value' => 'If you are in immediate danger, call the police or emergency services now.'],
            'fr' => ['value' => 'Si vous êtes en danger immédiat, appelez la police ou les services d\'urgence maintenant.'],
        ]);
    }
}
