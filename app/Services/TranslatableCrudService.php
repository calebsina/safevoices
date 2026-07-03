<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * CRUD service for any model using the Translatable trait.
 *
 * Splits the incoming payload into base-table attributes and the
 * per-locale "translations" block, persisting both atomically. Every
 * reference-data and CMS service extends this class, which is what
 * keeps ~20 translatable entities maintainable with almost no code.
 */
abstract class TranslatableCrudService extends BaseService
{
    protected array $with = ['translations'];

    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data) {
            $translations = $data['translations'] ?? [];
            unset($data['translations']);

            $model = $this->model::create($data);
            $model->syncTranslations($translations);

            $this->flushCaches();

            return $model->load('translations');
        });
    }

    public function update(Model|string|int $model, array $data): Model
    {
        $model = $model instanceof Model ? $model : $this->find($model);

        return $this->transaction(function () use ($model, $data) {
            $translations = $data['translations'] ?? null;
            unset($data['translations']);

            $model->update($data);

            if ($translations !== null) {
                $model->syncTranslations($translations);
            }

            $this->flushCaches();

            return $model->refresh()->load('translations');
        });
    }

    public function delete(Model|string|int $model): bool
    {
        $deleted = parent::delete($model);
        $this->flushCaches();

        return $deleted;
    }

    /**
     * Hook for services whose data is cached (locales, settings, ...).
     */
    protected function flushCaches(): void
    {
        //
    }
}
