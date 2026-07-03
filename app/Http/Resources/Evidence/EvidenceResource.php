<?php

namespace App\Http\Resources\Evidence;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Evidence metadata. storage_path is never exposed (model-hidden too). */
class EvidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'original_filename' => $this->original_filename,
            'mime_type'         => $this->mime_type,
            'file_size'         => $this->file_size,
            'integrity_hash'    => $this->integrity_hash,
            'scan_status'       => $this->scan_status,
            'is_csam_flagged'   => $this->is_csam_flagged,
            'uploaded_at'       => $this->uploaded_at?->toIso8601String(),
        ];
    }
}
