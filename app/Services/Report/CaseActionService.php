<?php

namespace App\Services\Report;

use App\Models\Communication\CaseAction;
use App\Models\Report\Report;
use App\Models\User\User;
use App\Services\Audit\AuditLogger;
use App\Services\BaseService;

/** Interventions recorded on a case (module E). */
class CaseActionService extends BaseService
{
    protected string $model = CaseAction::class;

    public function record(Report $report, User $user, string $actionType, ?string $notes): CaseAction
    {
        $action = $report->actions()->create([
            'user_id'     => $user->id,
            'action_type' => $actionType,
            'notes'       => $notes,
        ]);

        AuditLogger::log('case.action_recorded', $report, $actionType);

        return $action->load('user:id,name');
    }
}
