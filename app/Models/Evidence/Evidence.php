<?php

namespace App\Models\Evidence;

use App\Enums\ScanStatus;
use App\Models\Reference\Channel;
use App\Models\Report\Report;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Evidence vault entry.
 *
 * - storage_path / original_filename are (lock) columns: app-layer
 *   encrypted because a filename can leak identity.
 * - integrity_hash: SHA-256 of the ORIGINAL bytes for tamper-evidence.
 * - Every read/download MUST write an audit_logs entry - enforced in
 *   EvidenceService, never bypass it.
 * - is_csam_flagged items are locked to a restricted legal-handling
 *   workflow (download blocked outside that procedure).
 */
class Evidence extends Model
{
    use HasUuids;

    protected $table = 'evidence';

    protected $fillable = [
        'report_id', 'storage_disk', 'storage_path', 'original_filename',
        'mime_type', 'file_size', 'integrity_hash', 'source_channel_id',
        'scan_status', 'is_csam_flagged', 'uploaded_at',
    ];

    protected $hidden = ['storage_path'];

    protected function casts(): array
    {
        return [
            'storage_path'      => 'encrypted',
            'original_filename' => 'encrypted',
            'scan_status'       => ScanStatus::class,
            'is_csam_flagged'   => 'boolean',
            'uploaded_at'       => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function sourceChannel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'source_channel_id');
    }
}
