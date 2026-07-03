<?php

namespace Database\Seeders;

use App\Models\Reference\AffectedPersonType;
use App\Models\Reference\Channel;
use App\Models\Reference\ReferralPartnerType;
use App\Models\Reference\Relationship;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed(Channel::class, [
            ['whatsapp', 'WhatsApp (Amie)', 'WhatsApp (Amie)'],
            ['web',      'Web portal',      'Portail web'],
            ['sms',      'SMS',             'SMS'],
            ['ussd',     'USSD',            'USSD'],
        ]);

        $this->seed(AffectedPersonType::class, [
            ['child', 'Child',      'Enfant'],
            ['woman', 'Woman',      'Femme'],
            ['man',   'Man',        'Homme'],
            ['other', 'Other',      'Autre'],
        ]);

        $this->seed(Relationship::class, [
            ['self',          'Myself',              'Moi-même'],
            ['family_member', 'Family member',       'Membre de la famille'],
            ['neighbor',      'Neighbor',            'Voisin(e)'],
            ['teacher',       'Teacher / educator',  'Enseignant(e) / éducateur(trice)'],
            ['community',     'Community member',    'Membre de la communauté'],
            ['other',         'Other',               'Autre'],
        ]);

        $this->seed(ReferralPartnerType::class, [
            ['health',       'Health services',        'Services de santé'],
            ['legal',        'Legal aid',              'Aide juridique'],
            ['psychosocial', 'Psychosocial support',   'Soutien psychosocial'],
            ['police',       'Police / gendarmerie',   'Police / gendarmerie'],
            ['shelter',      'Shelter',                'Hébergement d\'urgence'],
        ]);
    }

    private function seed(string $model, array $rows): void
    {
        foreach ($rows as $i => [$key, $en, $fr]) {
            $entry = $model::updateOrCreate(['key' => $key], ['sort_order' => $i + 1, 'is_active' => true]);
            $entry->syncTranslations([
                'en' => ['label' => $en],
                'fr' => ['label' => $fr],
            ]);
        }
    }
}
