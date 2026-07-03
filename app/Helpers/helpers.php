<?php

/*
|--------------------------------------------------------------------------
| Global helper functions
|--------------------------------------------------------------------------
| Autoloaded through composer.json "files". Keep this file small:
| anything with real logic belongs in a Service or a Helper class.
*/

use App\Models\Setting\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('sv_setting')) {
    /**
     * Read a platform setting by key with sane caching.
     * Translatable settings resolve in the current locale.
     */
    function sv_setting(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('sv.settings', function () {
            return Setting::with('translations')->get()->keyBy('key');
        });

        $setting = $settings->get($key);

        if (! $setting) {
            return $default;
        }

        return $setting->resolvedValue() ?? $default;
    }
}

if (! function_exists('sv_locales')) {
    /**
     * Cached list of active locale codes (['en', 'fr']).
     */
    function sv_locales(): array
    {
        return Cache::rememberForever('sv.locales', function () {
            return \App\Models\Localization\Locale::where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('code')
                ->all();
        });
    }
}
