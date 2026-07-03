<?php

namespace App\Services\Report;

use App\Models\Reference\CaseStatus;
use App\Models\Report\Report;
use App\Models\Role\Role;
use App\Models\User\User;
use App\Services\Audit\AuditLogger;
use App\Services\BaseService;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Staff-side case operations (modules D & E).
 *
 * Row-level visibility is applied through Report::scopeVisibleTo() on
 * every read, so the role matrix (dossier 3.5.1) cannot be bypassed by
 * a clever query string.
 */
class ReportService extends BaseService
{
    protected string $model = Report::class;

    protected array $with = [
        'status.translations', 'priorityLevel.translations',
        'category.translations', 'channel.translations',
        'assignee:id,name,email', 'office.translations',
    ];

    public function __construct(private readonly NotificationService $notifications) {}

    /** Case queue with filters (use cases 3.2.14 / 3.2.15). */
    public function queue(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->submitted()
            ->visibleTo($user)
            ->when($filters['status_id'] ?? null, fn ($q, $v) => $q->where('status_id', $v))
            ->when($filters['priority_level_id'] ?? null, fn ($q, $v) => $q->where('priority_level_id', $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['office_id'] ?? null, fn ($q, $v) => $q->where('office_id', $v))
            ->when($filters['assigned_to'] ?? null, fn ($q, $v) => $q->where('assigned_to', $v))
            ->when(isset($filters['is_urgent']), fn ($q) => $q->where('is_urgent', filter_var($filters['is_urgent'], FILTER_VALIDATE_BOOLEAN)))
            ->when(isset($filters['unassigned']), fn ($q) => $q->whereNull('assigned_to'))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('reference_code', 'ilike', "%$v%"))
            ->orderByDesc('is_urgent')
            ->orderBy('priority_level_id')
            ->orderBy('submitted_at')
            ->paginate(min($perPage, 100));
    }

    /** Full case view for staff (use case 3.2.16). */
    public function show(Report $report): Report
    {
        return $report->load(array_merge($this->with, [
            'statusHistory.toStatus.translations', 'statusHistory.changedBy:id,name',
            'evidence', 'actions.user:id,name', 'referrals.partnerType.translations',
            'duplicateLinks.linkedReport:id,reference_code,status_id',
            'consents',
        ]));
    }

    /** Status transition with immutable history + reporter notification. */
    public function updateStatus(Report $report, int $toStatusId, ?string $note, User $actor): Report
    {
        return $this->transaction(function () use ($report, $toStatusId, $note, $actor) {
            $from = $report->status_id;

            $report->update(['status_id' => $toStatusId]);

            $status = CaseStatus::findOrFail($toStatusId);

            if ($status->is_terminal) {
                $report->update(['resolved_at' => now()]);
            }

            $report->statusHistory()->create([
                'from_status_id' => $from,
                'to_status_id'   => $toStatusId,
                'changed_by'     => $actor->id,
                'note'           => $note,
            ]);

            AuditLogger::log('case.status_changed', $report, "Status -> {$status->key}");

            // Discreet reporter notification (consent enforced inside).
            $this->notifications->notifyReporterStatusChanged($report->refresh());

            return $report->load($this->with);
        });
    }

    /** Assign to a caseworker / office (supervisor, use case 3.2.17). */
    public function assign(Report $report, string $assigneeId, ?int $officeId, User $actor): Report
    {
        return $this->transaction(function () use ($report, $assigneeId, $officeId, $actor) {
            $assignee = User::findOrFail($assigneeId);

            $report->update([
                'assigned_to' => $assignee->id,
                'office_id'   => $officeId ?? $assignee->office_id ?? $report->office_id,
            ]);

            // Auto-advance to "assigned" if the case was still earlier in the flow.
            $assigned = CaseStatus::byKey(CaseStatus::ASSIGNED);
            if ($report->status && $report->status->sort_order < $assigned->sort_order) {
                $report->update(['status_id' => $assigned->id]);
                $report->statusHistory()->create([
                    'from_status_id' => $report->getOriginal('status_id'),
                    'to_status_id'   => $assigned->id,
                    'changed_by'     => $actor->id,
                    'note'           => 'Assigned to caseworker',
                ]);
            }

            AuditLogger::log('case.assigned', $report, "Assigned to {$assignee->name}");
            $this->notifications->notifyStaff($assignee, 'case_assigned', $report);

            return $report->load($this->with);
        });
    }

    /** Escalate as urgent and alert unit supervisors (use case 3.2.19). */
    public function escalate(Report $report, ?string $reason, User $actor): Report
    {
        $report->update(['is_urgent' => true]);

        AuditLogger::log('case.escalated', $report, $reason);

        $supervisors = User::where('is_active', true)
            ->whereHas('role', fn ($q) => $q->where('key', Role::SUPERVISOR))
            ->when($report->office_id, fn ($q) => $q->where('office_id', $report->office_id))
            ->get();

        foreach ($supervisors as $supervisor) {
            $this->notifications->notifyStaff($supervisor, 'urgent_escalation', $report);
        }

        return $report->load($this->with);
    }
}
