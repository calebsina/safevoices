<?php

namespace App\Http\Resources\Report;

use App\Http\Resources\Evidence\EvidenceResource;
use App\Http\Resources\Reference\CaseCategoryResource;
use App\Http\Resources\Reference\CaseStatusResource;
use App\Http\Resources\Reference\PriorityLevelResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Staff-facing case payload.
 *
 * ANONYMITY WALL: this resource never includes reporterIdentity,
 * pin_hash or any phone-derived field. Caseworkers work with the
 * reference code only - by design, not by omission.
 */
class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'reference_code' => $this->reference_code,
            'locale'         => $this->locale,
            'reporting_for'  => $this->reporting_for,
            'description'    => $this->description,     // reporter's own words (single-locale)
            'incident_area'  => $this->incident_area,
            'incident_at'    => $this->incident_at?->toIso8601String(),
            'is_urgent'      => $this->is_urgent,
            'priority_score' => $this->priority_score,
            'submitted_at'   => $this->submitted_at?->toIso8601String(),
            'resolved_at'    => $this->resolved_at?->toIso8601String(),

            'status'   => new CaseStatusResource($this->whenLoaded('status')),
            'priority' => new PriorityLevelResource($this->whenLoaded('priorityLevel')),
            'category' => new CaseCategoryResource($this->whenLoaded('category')),
            'channel'  => $this->whenLoaded('channel', fn () => [
                'key'   => $this->channel->key,
                'label' => $this->channel->t('label'),
            ]),
            'affected_person_type' => $this->whenLoaded('affectedPersonType', fn () => $this->affectedPersonType->t('label')),
            'relationship'         => $this->whenLoaded('relationship', fn () => $this->relationship->t('label')),

            'assignee' => $this->whenLoaded('assignee', fn () => [
                'id'   => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),
            'office' => $this->whenLoaded('office', fn () => [
                'id'   => $this->office->id,
                'name' => $this->office->t('name'),
            ]),

            'evidence' => EvidenceResource::collection($this->whenLoaded('evidence')),

            'status_history' => $this->whenLoaded('statusHistory', fn () => $this->statusHistory->map(fn ($h) => [
                'to_status'  => $h->toStatus?->t('label'),
                'changed_by' => $h->changedBy?->name,
                'note'       => $h->note,
                'at'         => $h->created_at?->toIso8601String(),
            ])),

            'duplicate_links' => $this->whenLoaded('duplicateLinks', fn () => $this->duplicateLinks->map(fn ($l) => [
                'reference_code' => $l->linkedReport?->reference_code,
                'confidence'     => $l->confidence,
            ])),

            'consents' => $this->whenLoaded('consents', fn () => $this->consents->map(fn ($c) => [
                'data_use_consent' => $c->data_use_consent,
                'contact_consent'  => $c->contact_consent,
                'captured_at'      => $c->captured_at?->toIso8601String(),
            ])),
        ];
    }
}
