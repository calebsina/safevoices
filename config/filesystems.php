<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
        ],

        // Public CMS media - completely separate from the evidence vault.
        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        /*
        | Encrypted evidence vault. Contents are additionally encrypted at
        | the application layer by EvidenceService before writing, so a
        | leaked disk never exposes plaintext files. Swap the driver to
        | "s3" (or a MinIO endpoint) for in-country object storage without
        | touching application code.
        */
        'evidence' => [
            'driver' => 'local',
            'root'   => storage_path('app/evidence'),
            'throw'  => true,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
