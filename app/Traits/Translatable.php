<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Implements the "base table + <singular>_translations" pattern from
 * the SafeVoice data dictionary (Appendix 2, section 2).
 *
 * Conventions:
 *  - The translation model lives next to the base model and is named
 *    "<Model>Translation" (override with $translationModel).
 *  - The FK on the translation table is "<model_snake>_id"
 *    (override with $translationForeignKey).
 *
 * Usage:
 *  $category->t('name')                 -> current locale, falls back to default
 *  $category->t('name', 'fr')           -> explicit locale
 *  $category->syncTranslations([...])   -> upsert per-locale rows
 */
trait Translatable
{
    public function translations(): HasMany
    {
        return $this->hasMany($this->translationModelClass(), $this->translationForeignKey());
    }

    /**
     * Resolve the translation row for a locale, falling back to the
     * application fallback locale when missing.
     */
    public function translation(?string $locale = null): ?Model
    {
        $locale ??= app()->getLocale();

        $translations = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        return $translations->firstWhere('locale', $locale)
            ?? $translations->firstWhere('locale', config('app.fallback_locale', 'en'));
    }

    /**
     * Shorthand accessor for a translated field.
     */
    public function t(string $field, ?string $locale = null): mixed
    {
        return $this->translation($locale)?->{$field};
    }

    /**
     * Upsert translations from a ["en" => [...], "fr" => [...]] payload.
     */
    public function syncTranslations(array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $this->translations()->updateOrCreate(
                ['locale' => $locale],
                array_filter($fields, fn ($v) => $v !== null)
            );
        }
    }

    /**
     * Eager-load translations (use everywhere to avoid N+1 queries).
     */
    public function scopeWithTranslations(Builder $query): Builder
    {
        return $query->with('translations');
    }

    protected function translationModelClass(): string
    {
        return $this->translationModel ?? static::class.'Translation';
    }

    protected function translationForeignKey(): string
    {
        return $this->translationForeignKey ?? Str::snake(class_basename($this)).'_id';
    }
}
