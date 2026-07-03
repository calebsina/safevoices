<?php

namespace App\Models\Report;

use App\Enums\ReportingFor;
use App\Models\Communication\CaseAction;
use App\Models\Communication\CaseMessage;
use App\Models\Communication\CaseStatusHistory;
use App\Models\Communication\Referral;
use App\Models\Consent\Consent;
use App\Models\Evidence\Evidence;
use App\Models\Office\Office;
use App\Models\Reference\AffectedPersonType;
use App\Models\Reference\CaseCategory;
use App\Models\Reference\CaseStatus;
use App\Models\Reference\Channel;
use App\Models\Reference\PriorityLevel;
use App\Models\Reference\Relationship;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The core case record.
 *
 * Named `reports` (never `cases` - reserved SQL keyword). Narrative
 * content (description, incident_area) is single-locale: it is the
 * reporter's own words and is deliberately never machine-translated.
 * All surrounding labels (category, status, priority) come from
 * translatable reference tables.
 */
class Report extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'reference_code', 'pin_hash', 'reporter_identity_id', 'channel_id',
        'locale', 'affected_person_type_id', 'relationship_id', 'reporting_for',
        'category_id', 'description', 'incident_area', 'incident_at',
        'priority_level_id', 'priority_score', 'is_urgent', 'status_id',
        'assigned_to', 'office_id', 'linked_parent_report_id',
        'submitted_at', 'resolved_at',
    ];

    protected $hidden = ['pin_hash'];

    protected function casts(): array
    {
        return [
            'reporting_for' => ReportingFor::class,
            'is_urgent'     => 'boolean',
            'incident_at'   => 'datetime',
            'submitted_at'  => 'datetime',
            'resolved_at'   => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

    public function reporterIdentity(): BelongsTo
    {
        return $this->belongsTo(ReporterIdentity::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function affectedPersonType(): BelongsTo
    {
        return $this->belongsTo(AffectedPersonType::class);
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(Relationship::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CaseCategory::class, 'category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CaseStatus::class, 'status_id');
    }

    public function priorityLevel(): BelongsTo
    {
        return $this->belongsTo(PriorityLevel::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function linkedParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'linked_parent_report_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CaseMessage::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(CaseStatusHistory::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(CaseAction::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    public function duplicateLinks(): HasMany
    {
        return $this->hasMany(DuplicateLink::class);
    }

    // ------------------------------------------------------------------
    // Scopes
    // ------------------------------------------------------------------

    /** Only finalised reports (intake completed). */
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->whereNotNull('submitted_at');
    }

    /**
     * Row-level visibility per the role/feature matrix (dossier 3.5.1):
     * caseworker -> assigned cases, supervisor -> unit cases, admin -> all.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match (true) {
            $user->isAdministrator() => $query,
            $user->isSupervisor()    => $query->where('office_id', $user->office_id),
            default                  => $query->where('assigned_to', $user->id),
        };
    }
}
