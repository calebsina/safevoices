<?php

namespace App\Policies;

use App\Models\Report\Report;
use App\Models\User\User;

/**
 * Row-level access per the role/feature matrix (dossier 3.5.1):
 *
 *  administrator -> everything
 *  supervisor    -> cases in their office (unit)
 *  caseworker    -> cases assigned to them
 *
 * Assignment is a supervisor/administrator capability, additionally
 * gated by the `case.assign` permission.
 */
class ReportPolicy
{
    public function view(User $user, Report $report): bool
    {
        return $this->inScope($user, $report);
    }

    public function update(User $user, Report $report): bool
    {
        return $this->inScope($user, $report);
    }

    public function assign(User $user, Report $report): bool
    {
        if (! $user->hasPermission('case.assign')) {
            return false;
        }

        return $user->isAdministrator()
            || ($user->isSupervisor() && ($report->office_id === null || $report->office_id === $user->office_id));
    }

    private function inScope(User $user, Report $report): bool
    {
        return match (true) {
            $user->isAdministrator() => true,
            $user->isSupervisor()    => $report->office_id === null || $report->office_id === $user->office_id,
            default                  => $report->assigned_to === $user->id,
        };
    }
}
