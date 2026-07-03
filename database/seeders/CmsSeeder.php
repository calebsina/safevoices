<?php

namespace Database\Seeders;

use App\Models\Cms\ContentBlock;
use App\Models\Cms\Faq;
use App\Models\Cms\Menu;
use App\Models\Cms\Page;
use App\Models\Setting\UiString;
use Illuminate\Database\Seeder;

/** Minimal CMS demo content so the public endpoints answer immediately. */
class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // --- Home page with a hero block ---
        $home = Page::updateOrCreate(
            ['slug' => 'home'],
            ['key' => 'home', 'template' => 'landing', 'status' => 'published', 'is_system' => true, 'published_at' => now()]
        );

        $home->syncTranslations([
            'en' => ['title' => 'SafeVoice', 'meta_description' => 'Report abuse safely and anonymously.'],
            'fr' => ['title' => 'SafeVoice', 'localized_slug' => 'accueil', 'meta_description' => 'Signalez les abus en toute sécurité et anonymement.'],
        ]);

        $hero = ContentBlock::updateOrCreate(
            ['page_id' => $home->id, 'key' => 'hero'],
            ['type' => 'hero', 'sort_order' => 1, 'is_active' => true]
        );

        $hero->syncTranslations([
            'en' => [
                'heading'   => 'Your voice matters. Your identity stays yours.',
                'subheading' => 'Report abuse safely through WhatsApp or this portal - no name, no account.',
                'cta_label' => 'Start a report',
                'cta_url'   => '/report',
            ],
            'fr' => [
                'heading'   => 'Votre voix compte. Votre identité vous appartient.',
                'subheading' => 'Signalez un abus en toute sécurité via WhatsApp ou ce portail - sans nom, sans compte.',
                'cta_label' => 'Commencer un signalement',
                'cta_url'   => '/signaler',
            ],
        ]);

        // --- Header menu ---
        $menu = Menu::updateOrCreate(['key' => 'header'], ['is_active' => true]);
        $item = $menu->items()->updateOrCreate(['menu_id' => $menu->id, 'page_id' => $home->id], ['sort_order' => 1, 'is_active' => true]);
        $item->syncTranslations([
            'en' => ['label' => 'Home'],
            'fr' => ['label' => 'Accueil'],
        ]);

        // --- One FAQ ---
        $faq = Faq::updateOrCreate(['category' => 'general', 'sort_order' => 1], ['is_active' => true]);
        $faq->syncTranslations([
            'en' => ['question' => 'Do I have to give my name?', 'answer' => 'No. You never need to give your name. You receive a case code and PIN to follow up anonymously.'],
            'fr' => ['question' => 'Dois-je donner mon nom ?', 'answer' => 'Non. Vous n\'avez jamais besoin de donner votre nom. Vous recevez un code de dossier et un PIN pour un suivi anonyme.'],
        ]);

        // --- UI strings (second i18n layer) ---
        $strings = [
            ['portal.followup.title', 'portal', 'Track my report', 'Suivre mon signalement'],
            ['bot.greeting', 'bot', 'Hello, I\'m Amie. I\'m here to listen, safely and confidentially.', 'Bonjour, je suis Amie. Je suis là pour vous écouter, en toute sécurité et confidentialité.'],
        ];

        foreach ($strings as [$key, $group, $en, $fr]) {
            $string = UiString::updateOrCreate(['key' => $key], ['group' => $group]);
            $string->syncTranslations([
                'en' => ['value' => $en],
                'fr' => ['value' => $fr],
            ]);
        }
    }
}
