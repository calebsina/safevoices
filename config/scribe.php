<?php

use Knuckles\Scribe\Config\AuthIn;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Extracting\Strategies;
use function Knuckles\Scribe\Config\{configureStrategy};

return [
    'title' => 'SafeVoice API',
    'description' => 'Confidential Abuse & GBV Reporting Platform — REST API. '
        .'Reporters are anonymous (case code + PIN); staff authenticate with JWT.',
    'base_url' => config('app.url'),

    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [],
        ],
    ],

    'type' => 'laravel',
    'theme' => 'default',

    'laravel' => [
        'add_routes' => true,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
    ],

    'external' => ['html_attributes' => []],

    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    'auth' => [
        'enabled' => true,
        'default' => false,
        'in' => AuthIn::BEARER->value,
        'name' => 'Authorization',
        'use_value' => env('SCRIBE_AUTH_KEY'),
        'placeholder' => '{JWT_TOKEN}',
        'extra_info' => 'Obtain a token from <code>POST /api/v1/auth/login</code>. '
            .'Reporter (public) endpoints use X-Case-Code / X-Case-Pin headers instead.',
    ],

    'intro_text' => <<<'INTRO'
This documentation covers the three access tiers of the SafeVoice API:

- **Public** — locales, reference data, CMS content, guided intake, anonymous follow-up.
- **Staff (JWT)** — case queue, evidence, messaging, actions, referrals.
- **Admin (JWT + permission)** — users, roles, offices, reference data, CMS, settings, audit trail.
INTRO,

    'example_languages' => ['bash', 'javascript', 'php'],

    'postman' => ['enabled' => true, 'overrides' => []],
    'openapi' => ['enabled' => true, 'overrides' => [], 'generators' => []],

    'groups' => [
        'default' => 'Endpoints',
        'order' => [
            'Public / Report intake',
            'Public / Anonymous follow-up',
            'Staff / Authentication',
            'Staff / Cases',
            'Staff / Evidence',
            'Staff / Case messages',
            'Staff / Case actions',
            'Staff / Referrals',
            'Reference data',
            'Localization',
            'CMS',
            'Consent',
            'Admin / Users',
            'Admin / Roles & permissions',
            'Admin / Offices',
            'Admin / Settings',
            'Admin / Notification templates',
            'Admin / Audit trail',
        ],
    ],

    'logo' => false,
    'last_updated' => 'Last updated: {date:F j, Y}',
    'examples' => [
        'faker_seed' => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    'strategies' => [
        'metadata' => [...Defaults::METADATA_STRATEGIES],
        'headers' => [
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data: [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]),
        ],
        'urlParameters' => [...Defaults::URL_PARAMETERS_STRATEGIES],
        'queryParameters' => [...Defaults::QUERY_PARAMETERS_STRATEGIES],
        'bodyParameters' => [...Defaults::BODY_PARAMETERS_STRATEGIES],
        'responses' => configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(
                only: [],
                config: ['app.debug' => false],
            )
        ),
        'responseFields' => [...Defaults::RESPONSE_FIELDS_STRATEGIES],
    ],

    'database_connections_to_transact' => [config('database.default')],
    'fractal' => ['serializer' => null],
];
