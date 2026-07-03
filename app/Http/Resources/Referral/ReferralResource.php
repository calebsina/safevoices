<?php

namespace App\Http\Resources\Referral;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'partner_type' => $this->whenLoaded('partnerType', fn () => $this->partnerType->t('label')),
            'partner_name' => $this->partner_name,
            'status'       => $this->status,
            'notes'        => $this->notes,
            'referred_at'  => $this->referred_at?->toIso8601String(),
        ];
    }
}
