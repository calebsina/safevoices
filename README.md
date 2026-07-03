# SafeVoice API

Confidential Abuse & GBV Reporting Platform — REST API (Laravel 12 · PHP 8.3 · PostgreSQL).

---

## 1. Architecture at a glance

```
app/
├── Enums/                     Backed enums (SenderType, ScanStatus, ActorType, ...)
├── Helpers/                   ApiResponse envelope · ReferenceCode generator · helpers.php (sv_setting, sv_locales)
├── Traits/Translatable.php    base + <singular>_translations pattern (t(), syncTranslations())
├── Http/
│   ├── Middleware/            SetLocale · RequirePermission · ResolveFollowUpCase · ResolveDraftReport
│   ├── Controllers/Api/       BaseController + V1/{Auth,User,Role,...} — thin, Scribe-annotated
│   ├── Requests/              BaseFormRequest (+ translationRules()) + module folders
│   └── Resources/             Module folders; translated labels via ->t('field')
├── Models/                    Module folders (User/User, Report/Report, Cms/Page, ...)
├── Services/                  BaseService · TranslatableCrudService + module folders (all business logic)
├── Policies/ReportPolicy.php  Row-level role matrix (caseworker/supervisor/admin)
└── Providers/
```

**Layering rule:** Controller → FormRequest (validation) → Service (business logic + transactions) → Model. Resources shape output; `ApiResponse` guarantees one envelope everywhere:
`{ "success": bool, "message": "...", "data": ..., "errors": ..., "meta": ... }`

### OOP building blocks

- **`BaseController`** — `ok() / created() / deleted() / fail() / paginated()`
- **`BaseFormRequest`** — JSON 422 envelope + `translationRules()` for per-locale payloads
- **`BaseService`** — generic CRUD, `query()`, `transaction()`
- **`TranslatableCrudService`** — splits the `translations` block, persists atomically, cache-flush hook. ~20 translatable entities each need only a 5-line service.
- **`Translatable` trait** — `translations()`, `t('field', 'fr')`, `syncTranslations()`, locale fallback.

### Two-layer i18n (per the data dictionary)

1. **Developer strings** → `lang/en.json` + `lang/fr.json` (`__('messages...')`).
2. **Admin-editable content** → DB translation tables (`ui_strings`, `pages`, `case_statuses`, ...). Adding a language = inserting one `locales` row; no migration.

Locale negotiation: `?lang=` → `X-Locale` → `Accept-Language` → default. Reporter free text (description, messages) is **never** translated — single-locale by design.

### Security model (dossier §4)

- **Staff:** JWT (`tymon/jwt-auth`), optional TOTP MFA (`pragmarx/google2fa`), RBAC permission middleware + `ReportPolicy` row scope (assigned / unit / all).
- **Reporters:** no account. Per-case `X-Case-Code` + `X-Case-Pin` (hashed, throttled, every attempt logged, identical error for wrong code vs wrong PIN).
- **Intake drafts:** one-off `X-Draft-Token` (hashed in cache, 24 h TTL).
- **Phone numbers:** SHA-256 hash (dedup) + encrypted copy (delivery only) — never exposed to staff.
- **Evidence vault:** SHA-256 integrity hash of original bytes → app-layer encryption → private disk; every view/download audited; CSAM-flagged items refuse normal download.
- **Audit trail:** append-only via `AuditLogger::log()` — IPs stored hashed.

## 2. Setup

The repo ships application source only (no `vendor/`).

```bash
cp .env.example .env            # set your PostgreSQL credentials
composer install
php artisan key:generate
php artisan jwt:secret          # tymon/jwt-auth secret
php artisan migrate --seed      # schema + locales, RBAC, reference data, admin, CMS demo
php artisan storage:link
php artisan serve
```

Seeded administrator: `admin@safevoice.cm` / `ChangeMe-Please-1!` — **change it and enable MFA immediately.**

> Note: `tymon/jwt-auth ^2.2` supports Laravel 11/12. If you prefer the actively maintained fork, swap in `php-open-source-saver/jwt-auth` — the API is identical (`Tymon\` → `PHPOpenSourceSaver\` namespace and the `jwt` guard driver name stays the same).

## 3. API documentation (Scribe)

```bash
php artisan scribe:generate
```

Docs at **`/docs`** (+ Postman collection & OpenAPI spec). Groups are ordered public → staff → admin in `config/scribe.php`.

## 4. Quick tour of the flows

**Anonymous intake** (`POST /api/v1/intake/start` → returns `report_id` + one-off `X-Draft-Token`):

```
PATCH /intake/{report}/consent   → active consent version recorded
PATCH /intake/{report}/context   → who is affected / relationship
PATCH /intake/{report}/incident  → category, description, area, danger flag
POST  /intake/{report}/evidence  → optional attachments
POST  /intake/{report}/submit    → returns reference_code + PIN (ONCE)
```

Submission triggers **priority scoring** (`priority_rules` → `priority_levels`) and **duplicate detection** (`duplicate_links`).

**Anonymous follow-up** (headers `X-Case-Code` + `X-Case-Pin`):

```
GET  /follow-up/status     POST /follow-up/information
GET  /follow-up/messages   POST /follow-up/messages   POST /follow-up/evidence
```

**Staff** (`Authorization: Bearer <jwt>`): `/reports` queue with filters, `/reports/{id}`, `PATCH .../status`, `PATCH .../assign`, `POST .../escalate`, evidence, messages, actions, referrals — each gated by permission keys (`case.assign`, `evidence.download`, ...) and `ReportPolicy`.

## 5. Integration points (deliberate stubs)

| Where                            | What to plug in                                          |
| -------------------------------- | -------------------------------------------------------- |
| `NotificationService::deliver()` | WhatsApp Cloud API / BSP + SMS gateway                   |
| `Evidence.scan_status`           | async malware / content scan job                         |
| `channels` seed (`whatsapp`)     | the Amie webhook adapter calls the same intake endpoints |

## 6. Conventions to keep

- Core case table is **`reports`** (never `cases` — reserved keyword).
- UUID PKs for sensitive rows (users, reports, evidence, messages, identities); bigint for reference/CMS.
- Never expose `ReporterIdentity`, `pin_hash` or `storage_path` in any staff-facing resource.
- Every new translatable entity: base table + `<singular>_translations` (unique `[fk, locale]`, FK `locale → locales.code`), model uses `Translatable`, service extends `TranslatableCrudService`.
