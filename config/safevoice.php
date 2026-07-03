<?php

/*
|--------------------------------------------------------------------------
| SafeVoice global configuration
|--------------------------------------------------------------------------
| Central place for all platform-level tunables so nothing magic is
| hard-coded across services. Everything can be overridden per
| environment through .env.
*/
return [

    // Public case reference code, e.g. SV-7F3K-9Q2.
    'reference_code' => [
        'prefix'   => 'SV',
        // Unambiguous alphabet (no 0/O, 1/I/L) - reporters read it over the phone.
        'alphabet' => '23456789ABCDEFGHJKMNPQRSTUVWXYZ',
        'groups'   => [4, 3], // SV-XXXX-XXX
    ],

    // Follow-up PIN issued together with the case code.
    'pin' => [
        'length' => 6,
    ],

    // Brute-force protection on the code + PIN follow-up endpoints.
    'follow_up' => [
        'max_attempts'  => (int) env('FOLLOWUP_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('FOLLOWUP_DECAY_SECONDS', 300),
    ],

    // Intake drafts: how long an unfinished report can be resumed.
    'intake' => [
        'draft_ttl_hours' => 24,
    ],

    // Evidence vault.
    'evidence' => [
        'disk'          => env('EVIDENCE_DISK', 'evidence'),
        'max_kilobytes' => (int) env('EVIDENCE_MAX_KB', 25600),
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'mp4', '3gp', 'mp3', 'ogg', 'aac', 'amr', 'pdf', 'doc', 'docx'],
    ],

    // Duplicate detection window (days) and minimum confidence to link.
    'triage' => [
        'duplicate_window_days'    => 14,
        'duplicate_min_confidence' => 60,
        'recent_incident_hours'    => 48,
    ],
];
