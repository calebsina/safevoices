<?php

namespace App\Services\Locale;

use App\Models\Localization\Locale;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Locale management. Adding a row is all that is needed to enable a new
 * language platform-wide (no migration).
 */
class LocaleService extends BaseService
{
    protected string $model = Locale::class;

    public function create(array $data): Model
    {
        $locale = parent::create($data);
        $this->flush();

        return $locale;
    }

    public function update(Model|string|int $model, array $data): Model
    {
        return $this->transaction(function () use ($model, $data) {
            $locale = $model instanceof Model ? $model : $this->find($model);

            // Keep the "exactly one default" invariant from the dictionary.
            if (($data['is_default'] ?? false) === true) {
                Locale::where('id', '!=', $locale->id)->update(['is_default' => false]);
            }

            $locale->update($data);
            $this->flush();

            return $locale->refresh();
        });
    }

    private function flush(): void
    {
        Cache::forget('sv.locales');
    }
}
