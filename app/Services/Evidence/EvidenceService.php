<?php

namespace App\Services\Evidence;

use App\Enums\ActorType;
use App\Enums\ScanStatus;
use App\Models\Evidence\Evidence;
use App\Models\Reference\Channel;
use App\Models\Report\Report;
use App\Services\Audit\AuditLogger;
use App\Services\BaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Evidence vault (module B).
 *
 * Chain of custody:
 *  1. SHA-256 integrity hash of the ORIGINAL bytes (tamper evidence)
 *  2. application-layer encryption of the contents (Crypt) before the
 *     file ever touches the disk - a leaked volume exposes nothing
 *  3. random storage path, encrypted DB columns for path/filename
 *  4. EVERY view/download writes an audit_logs entry - no exceptions
 *  5. CSAM-flagged items refuse download outside the restricted
 *     legal-handling workflow
 */
class EvidenceService extends BaseService
{
    protected string $model = Evidence::class;

    /** Attach an uploaded file to a report (reporter or staff side). */
    public function store(Report $report, UploadedFile $file, string $channelKey, ActorType $actor = ActorType::Reporter): Evidence
    {
        $disk = config('safevoice.evidence.disk');
        $originalBytes = file_get_contents($file->getRealPath());

        // 1. Integrity hash BEFORE encryption - provable chain of custody.
        $integrityHash = hash('sha256', $originalBytes);

        // 3. Random, identity-free object key.
        $path = now()->format('Y/m').'/'.Str::uuid().'.bin';

        // 2. Encrypt-then-write.
        Storage::disk($disk)->put($path, Crypt::encrypt($originalBytes));

        $evidence = $this->transaction(fn () => Evidence::create([
            'report_id'         => $report->id,
            'storage_disk'      => $disk,
            'storage_path'      => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType() ?? 'application/octet-stream',
            'file_size'         => $file->getSize(),
            'integrity_hash'    => $integrityHash,
            'source_channel_id' => Channel::where('key', $channelKey)->firstOrFail()->id,
            'scan_status'       => ScanStatus::Pending, // async malware/content scan hook
            'uploaded_at'       => now(),
        ]));

        AuditLogger::log('evidence.uploaded', $evidence, null, ['report' => $report->reference_code], $actor);

        return $evidence;
    }

    /**
     * Decrypt and return the plaintext contents for a staff download.
     * MANDATORY audit entry on every call (dossier 3.2.8 / section 6).
     *
     * @return array{0:string,1:Evidence} [plaintext bytes, evidence]
     */
    public function download(Evidence $evidence): array
    {
        // Restricted workflow: normal downloads are refused for CSAM items.
        if ($evidence->is_csam_flagged) {
            AuditLogger::log('evidence.download_blocked_csam', $evidence);
            throw new AccessDeniedHttpException(__('messages.evidence.csam_restricted'));
        }

        $contents = Crypt::decrypt(
            Storage::disk($evidence->storage_disk)->get($evidence->storage_path)
        );

        // Tamper check against the original hash.
        if (hash('sha256', $contents) !== $evidence->integrity_hash) {
            AuditLogger::log('evidence.integrity_failure', $evidence);
            abort(409, __('messages.evidence.integrity_failed'));
        }

        AuditLogger::log('evidence.downloaded', $evidence);

        return [$contents, $evidence];
    }

    /** Audited metadata listing for a case file. */
    public function forReport(Report $report)
    {
        AuditLogger::log('evidence.viewed', $report, 'Evidence list viewed');

        return $report->evidence()->latest('uploaded_at')->get();
    }
}
