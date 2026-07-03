<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reference data (Appendix 2, section 5). All translatable entities
 * follow the base + <singular>_translations pattern.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();   // physical_abuse, sexual_abuse, ...
            $table->foreignId('parent_id')->nullable()->constrained('case_categories')->nullOnDelete();
            $table->integer('severity_weight')->default(0); // feeds priority score
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->translations('case_category_translations', 'case_category_id', 'case_categories', function (Blueprint $table) {
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
        });

        Schema::create('case_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();   // submitted ... resolved
            $table->integer('sort_order')->default(0);
            $table->boolean('is_terminal')->default(false);
            $table->string('color', 9)->nullable();
            $table->timestamps();
        });

        $this->translations('case_status_translations', 'case_status_id', 'case_statuses', function (Blueprint $table) {
            $table->string('label', 100);                    // staff wording
            $table->string('reporter_label', 100);           // simplified reporter wording
            $table->string('description', 255)->nullable();
        });

        Schema::create('priority_levels', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();   // urgent, high, medium, low
            $table->integer('score_min');
            $table->integer('score_max');
            $table->integer('sla_minutes');        // response-time target
            $table->string('color', 9)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $this->translations('priority_level_translations', 'priority_level_id', 'priority_levels', function (Blueprint $table) {
            $table->string('label', 100);
        });

        // Scoring config - pure config, NOT translatable.
        Schema::create('priority_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();   // imminent_danger, recency, ...
            $table->integer('weight')->default(1);
            $table->jsonb('conditions');           // signal -> points mapping
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Small lookup lists - identical base + translations shape.
        foreach (['affected_person_types', 'relationships', 'referral_partner_types', 'channels'] as $lookup) {
            Schema::create($lookup, function (Blueprint $table) {
                $table->id();
                $table->string('key', 64)->unique();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $singular = \Illuminate\Support\Str::singular($lookup);

            $this->translations("{$singular}_translations", "{$singular}_id", $lookup, function (Blueprint $table) {
                $table->string('label', 150);
                $table->string('description', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'channel_translations', 'channels',
            'referral_partner_type_translations', 'referral_partner_types',
            'relationship_translations', 'relationships',
            'affected_person_type_translations', 'affected_person_types',
            'priority_rules',
            'priority_level_translations', 'priority_levels',
            'case_status_translations', 'case_statuses',
            'case_category_translations', 'case_categories',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    /** Shared shape for every *_translations table in this migration. */
    private function translations(string $table, string $fk, string $references, \Closure $columns): void
    {
        Schema::create($table, function (Blueprint $t) use ($fk, $references, $columns) {
            $t->id();
            $t->foreignId($fk)->constrained($references)->cascadeOnDelete();
            $t->string('locale', 5);
            $columns($t);
            $t->unique([$fk, 'locale']);
            $t->foreign('locale')->references('code')->on('locales');
        });
    }
};
