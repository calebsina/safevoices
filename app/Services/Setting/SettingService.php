<?php

namespace App\Services\Setting;

use App\Models\Setting\Setting;
use App\Services\TranslatableCrudService;
use Illuminate\Support\Facades\Cache;

class SettingService extends TranslatableCrudService
{
    protected string $model = Setting::class;

    protected function flushCaches(): void
    {
        Cache::forget('sv.settings'); // sv_setting() helper cache
    }

    /** Whitelisted public settings for the SPA / landing page. */
    public function publicSettings(): array
    {
        $public = ['site.tagline', 'whatsapp.number', 'site.emergency_notice'];

        return collect($public)
            ->mapWithKeys(fn ($key) => [$key => sv_setting($key)])
            ->all();
    }
}
