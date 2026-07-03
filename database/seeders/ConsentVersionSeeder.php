<?php

namespace Database\Seeders;

use App\Models\Consent\ConsentVersion;
use Illuminate\Database\Seeder;

class ConsentVersionSeeder extends Seeder
{
    public function run(): void
    {
        $version = ConsentVersion::updateOrCreate(
            ['version' => '2026-06'],
            ['effective_from' => now(), 'is_active' => true]
        );

        $version->syncTranslations([
            'en' => ['body' => 'Your report is confidential. We will use the information you share only to review and act on your report. You may decline to be contacted; your report will still be processed. You will receive a case code and PIN to follow up anonymously.'],
            'fr' => ['body' => 'Votre signalement est confidentiel. Les informations partagées ne seront utilisées que pour examiner et traiter votre signalement. Vous pouvez refuser d\'être contacté(e) ; votre signalement sera tout de même traité. Vous recevrez un code de dossier et un PIN pour effectuer un suivi anonyme.'],
        ]);
    }
}
