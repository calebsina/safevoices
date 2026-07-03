<?php

namespace App\Services\Setting;

use App\Models\Setting\UiString;
use App\Services\TranslatableCrudService;

class UiStringService extends TranslatableCrudService
{
    protected string $model = UiString::class;

    /** Flat key -> value map for the requested locale (bot & SPA bootstrap). */
    public function forLocale(string $locale): array
    {
        return $this->query()->get()
            ->mapWithKeys(fn (UiString $s) => [$s->key => $s->t('value', $locale)])
            ->all();
    }
}
