# Museum Azman Technical Documentation

Version: 1.2  
Date: 2026-03-10  
System: Museum Azman (Laravel 12)

## 1. Document Purpose
This document provides complete technical guidance for developing, deploying, operating, and maintaining the Museum Azman system.

It is intended for:
- Developers
- DevOps and infrastructure engineers
- Technical support and operations teams
- System administrators

## 2. System Overview
Museum Azman is a web-based collection management platform for museum artwork operations. The system supports:
- Artwork inventory management
- Artist and location management
- Artwork movement tracking
- Reports and exports (CSV and PDF)
- User and role management
- Settings, timezone management, and backup operations
- Installable PWA behavior for mobile shortcut/app mode

## 3. Technology Stack
### 3.1 Backend
- PHP: `^8.2`
- Framework: `laravel/framework ^12.0`
- Key packages:
- `laravel/tinker`
- `barryvdh/laravel-dompdf` (for PDF fallback generation)
- `bupple/laravel-ai-engine` (optional, environment-configurable)

### 3.2 Frontend
- Blade templates (`resources/views`)
- Vite build pipeline
- Tailwind CSS 4
- Vanilla JavaScript for interactive features:
- AJAX flows (including JSON create/update responses)
- Modal interactions
- Search suggestions/autocomplete
- PWA/service worker behavior

### 3.3 Data and Storage
- Primary database: configurable via Laravel (`sqlite` default in `.env.example`), production typically MySQL
- Backup snapshot logic supports table extraction for `mysql`, `pgsql`, and `sqlite`
- File storage:
- `public` disk for images/assets (`storage/app/public`)
- `local` disk for private JSON backup snapshots (`storage/app/private/backups`)

## 4. Project Structure
Top-level structure:
- `app/` application logic
- `bootstrap/` app bootstrap and middleware aliases
- `config/` framework and feature configuration
- `database/` migrations, factories, seeders
- `public/` web root and PWA assets
- `resources/` Blade views, JS, CSS
- `routes/` HTTP and console routes
- `storage/` logs, caches, backups, uploaded files
- `tests/` unit and feature tests

Key implementation directories:
- Controllers: `app/Http/Controllers`
- Models: `app/Models`
- Services: `app/Services`
- Console Commands: `app/Console/Commands`
- Middleware: `app/Http/Middleware`

## 5. Architecture
### 5.1 Runtime Request Architecture
1. Client (browser/PWA) sends request.
2. Route resolution in `routes/web.php`.
3. Middleware enforcement (`guest`, `auth`, custom `admin`).
4. Controller orchestration in `app/Http/Controllers`.
5. Domain/model operations through Eloquent in `app/Models`.
6. Optional service invocation (`ImageOptimizer`).
7. Response rendering as Blade HTML, JSON, file download, or redirect.

### 5.2 Scheduled Operations Architecture
1. OS cron executes `php artisan schedule:run` every minute.
2. Laravel scheduler in `routes/console.php` resolves backup time and timezone from `settings` table.
3. `backup:auto` runs daily using configured time and timezone.
4. Backup file is written under `storage/app/private/backups`.

### 5.3 Layered Design
- Presentation layer: Blade templates and JS UI logic
- Application layer: controllers and form requests (`app/Http/Requests`)
- Domain layer: Eloquent models and relationships
- Service layer: reusable operation services (`ImageOptimizer`)
- Infrastructure layer: route config, scheduler, filesystem, database and environment config

## 6. Core Modules
### 6.1 Authentication Module
- Controller: `app/Http/Controllers/AuthController.php`
- Guest routes:
- `GET /login`
- `POST /login`
- Auth route:
- `POST /logout`

Sample code:
```php
// routes/web.php
Route::middleware('guest')->group(function () {
  Route::get('login', [AuthController::class, 'showLogin'])->name('login');
  Route::post('login', [AuthController::class, 'login'])->name('login.perform');
});

Route::middleware('auth')->group(function () {
  Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
```

### 6.2 Dashboard Module
- Controller: `app/Http/Controllers/DashboardController.php`
- Route: `GET /` (authenticated)
- Provides executive metrics (artwork counts, portfolio value, movement states, geography split).

Sample code:
```php
// routes/web.php
Route::middleware('auth')->group(function () {
  Route::get('/', DashboardController::class)->name('dashboard');
});

// app/Http/Controllers/DashboardController.php
$stats = [
  'total_artworks' => $artworks->count(),
  'collection_value' => (float) $artworks->sum('current_valuation'),
  'in_transit' => $artworks->where('status', 'In Transit')->count(),
];
```

### 6.3 Artwork Module
- Controller: `app/Http/Controllers/ArtworkController.php`
- Full resource routes for CRUD (`Route::resource('artworks', ...)`)
- Additional endpoints:
- `GET /artworks/suggestions`
- `GET /artworks/export/pdf`
- Handles image upload and optimization workflow
- Supports AJAX/JSON response paths for create/update
- PDF export uses headless Chrome when available, then falls back to DomPDF

Sample code:
```php
// routes/web.php
Route::get('artworks/suggestions', [ArtworkController::class, 'suggestions'])->name('artworks.suggestions');
Route::get('artworks/export/pdf', [ArtworkController::class, 'exportPdf'])->name('artworks.export.pdf');
Route::resource('artworks', ArtworkController::class);

// app/Http/Controllers/ArtworkController.php (store excerpt)
if ($request->hasFile('primary_image')) {
  $primary = $this->imageOptimizer->storeUploaded($request->file('primary_image'));
  $artwork->primary_image_path = $primary['path'] ?? null;
}

if ($request->expectsJson()) {
  return response()->json([
    'message' => 'Artwork created successfully.',
    'redirect_url' => route('artworks.show', $artwork),
  ]);
}
```

### 6.4 Artist Module
- Controller: `app/Http/Controllers/ArtistController.php`
- Route: `GET /artists`
- Includes portfolio aggregation, sorting, filtering, and suggestion metadata.

Sample code:
```php
// app/Http/Controllers/ArtistController.php (index excerpt)
$artists = Artist::query()
  ->withCount('artworks')
  ->withSum('artworks as portfolio_value', 'current_valuation')
  ->when($q !== '', function ($query) use ($q) {
    $like = '%'.Str::lower($q).'%';
    $query->whereRaw('LOWER(name) LIKE ?', [$like]);
  })
  ->orderBy('name')
  ->get();
```

### 6.5 Location Module
- Controller: `app/Http/Controllers/LocationController.php`
- Authenticated:
- `GET /locations`
- `GET /locations/{location}`
- Admin-only mutating routes:
- `GET /locations/create`
- `POST /locations`
- `GET /locations/{location}/edit`
- `PUT /locations/{location}`
- Includes map search and embed URL derivation for location display.

Sample code:
```php
// routes/web.php
Route::get('locations/create', [LocationController::class, 'create'])
  ->name('locations.create')
  ->middleware('admin');

Route::post('locations', [LocationController::class, 'store'])
  ->name('locations.store')
  ->middleware('admin');

// app/Http/Controllers/LocationController.php (map URL excerpt)
private function mapUrl(string $query): ?string
{
  if ($query === '') {
    return null;
  }

  return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
}
```

### 6.6 Movement Module
- Controller: `app/Http/Controllers/MovementController.php`
- Routes:
- `GET /movements`
- `POST /movements`
- Tracks movement and updates linked artwork status from movement status.

Sample code:
```php
// app/Http/Controllers/MovementController.php (store excerpt)
$movement = Movement::create($request->validated());

$movement->artwork()->update([
  'status' => $movement->status,
]);

return redirect()->back()->with('success', 'Movement recorded successfully.');
```

### 6.7 Reporting Module
- Controller: `app/Http/Controllers/ReportController.php`
- Routes:
- `GET /reports`
- `GET /reports/export/excel` (CSV stream)
- `GET /reports/export/pdf`
- Includes analytics dataset construction, chart-ready aggregates, and PDF generation with Chrome-first strategy.

Sample code:
```php
// app/Http/Controllers/ReportController.php (CSV export excerpt)
return response()->streamDownload(function () use ($stats) {
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Reports & Analytics Export']);
  fputcsv($out, ['Total Portfolio Value', number_format((float) $stats['total_value'], 2)]);
  fclose($out);
}, 'reports-analytics-'.now()->format('Ymd-His').'.csv', [
  'Content-Type' => 'text/csv; charset=UTF-8',
]);
```

### 6.8 Settings and Backup Module
- Controller: `app/Http/Controllers/SettingController.php`
- Routes:
- `GET /settings`
- `POST /settings/{section}`
- `POST /settings/backup/generate`
- `GET /settings/backup/download`
- `POST /settings/backup/delete`
- Features timezone-aware backup metadata, backup listing, and settings persistence in key-value table.

Sample code:
```php
// app/Http/Controllers/SettingController.php (backup generation excerpt)
$timezone = $this->getConfiguredTimezone();
$relativePath = $this->createBackupFile($timezone);

$this->saveSettings([
  'backup_last_file' => $relativePath,
  'backup_last_generated_at' => now($timezone)->toIso8601String(),
]);

return redirect()->route('settings.index', ['tab' => 'general'])
  ->with('success', 'Backup generated and saved to storage successfully.');
```

### 6.9 Admin User Module
- Controller: `app/Http/Controllers/AdminUserController.php`
- Route group: `/admin/*` + `admin` middleware
- Routes:
- `GET /admin/users`
- `POST /admin/users`
- `GET /admin/users/{user}/edit`
- `PUT /admin/users/{user}`
- Includes role assignment and optional avatar optimization/storage.

Sample code:
```php
// routes/web.php
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
  Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
  Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
});

// app/Http/Controllers/AdminUserController.php (store excerpt)
if ($request->hasFile('avatar')) {
  $stored = $this->imageOptimizer->storeUploaded($request->file('avatar'), 'avatars');
  $validated['avatar_path'] = $stored['path'] ?? null;
}

$validated['password'] = Hash::make($validated['password']);
User::query()->create($validated);
```

### 6.10 Sorting and Table UX
The listing modules support query-based sorting with strict allowlists in controllers and clickable table headers in Blade views.

Implemented sortable columns:
- `Admin Users` (`/admin/users`): `name`, `email`, `role`
- `Settings > Users & Roles` (`/settings?tab=users-roles`): `name`, `email`, `role`
- `Collection` table view (`/artworks?view=table`): `title`, `current_valuation`, `created_at` (default)
- `Locations` list view (`/locations?view=list`): `name`, `type`
- `Movement History` (`/movements`): `artwork_title`, `from_location`, `to_location`, `date_out`, `expected_return_date`, `responsible_handler`, `reason`, `status`

Sorting conventions:
- Query parameters: `sort` and `direction` (`asc` or `desc`)
- Active sort indicator: arrow marker in header (`▲` or `▼`)
- Existing filters and view mode are preserved when changing sort order
- Related model sort fields (for example role/artwork title) are handled via controlled joins with `select(base_table.*)`

## 7. Data Model
Primary models and relationships:
- `User` belongsTo `Role` (`roleRelation`)
- `Role` hasMany `User`
- `Artist` hasMany `Artwork`
- `Location` hasMany `Artwork`
- `Artwork` belongsTo `Artist`
- `Artwork` belongsTo `Location`
- `Artwork` hasMany `ArtworkImage`
- `Artwork` hasMany `Movement`
- `ArtworkImage` belongsTo `Artwork`
- `Movement` belongsTo `Artwork`
- `Setting` is key-value configuration storage

Notable model behavior:
- `User::isAdmin()` treats both `admin` and `owner` as privileged
- `Artwork` exposes computed `primary_image_url`
- `ArtworkImage` exposes computed `url`
- `User` exposes computed `avatar_url`

## 8. Routing and Access Control
Routes files:
- `routes/web.php`
- `routes/console.php`

Access boundaries:
- `guest` middleware:
- login routes only
- `auth` middleware:
- main application routes
- `admin` middleware (`App\Http\Middleware\AdminOnly`):
- privileged mutating routes and admin user management

Middleware wiring:
- Alias configured in `bootstrap/app.php`
- Proxy trust configured with `trustProxies('*')` for tunnel/reverse proxy compatibility

PWA manifest route:
- `GET /manifest.json` returns `public/manifest.json` via file response

Operational endpoint note:
- `GET /optimize-clear` runs optimize/cache/config/route/view clear commands and is currently publicly reachable in `routes/web.php`.

## 9. File and Image Handling
Service: `app/Services/ImageOptimizer.php`

Capabilities:
- Store uploaded image from `UploadedFile`
- Store image from URL via HTTP client (`--download-images` import option)
- Resize/compress image via GD to max side 1800px
- Save optimized output as WebP or JPEG
- Fallback to original binary storage for unsupported transforms

Storage locations:
- Public disk path (`storage/app/public/...`) for web-accessible assets
- Backup path on local disk (`storage/app/private/backups`)

## 10. Backup and Recovery
### 10.1 Manual Backup
Triggered from settings routes:
- `POST /settings/backup/generate`

Output:
- JSON snapshot under `storage/app/private/backups`

### 10.2 Automatic Backup
Command:
- `php artisan backup:auto`

Scheduler definition:
- `routes/console.php`

Behavior:
- Runs daily at configured `backup_auto_time` and `timezone`
- Uses `withoutOverlapping()` lock behavior

### 10.3 Backup Content
Backup JSON payload includes:
- `generated_at`
- `timezone`
- `connection`
- `tables` (full row snapshot by table)

### 10.4 Restore Status
- A complete automated restore flow is not currently implemented as an exposed production feature.
- Recovery is manual from backup payloads.

## 11. PWA and Mobile Installability
Relevant files:
- `public/manifest.json`
- `public/sw.js`
- `public/icons/`
- `<head>` tags in `resources/views/layouts/app.blade.php`

Current behavior:
- App can be installed from supported mobile browsers.
- Manifest sets `display: standalone` and includes maskable icons.
- Service worker uses cache-first fetch strategy for predefined core files.
- URL bar hides only when launched as installed app (browser/device dependent).

## 12. Environment Configuration
Template file:
- `.env.example`

Important groups:
- Core app:
- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`, `APP_DEBUG`
- Database:
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Sessions/cache/queue:
- `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION`
- Mail:
- `MAIL_*`
- Filesystem/cloud:
- `FILESYSTEM_DISK`, `AWS_*`
- AI engine (optional):
- `BUPPLE_*`

## 13. Build and Run
### 13.1 Initial Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

Equivalent composer helper script:
```bash
composer run setup
```

### 13.2 Local Development
```bash
composer run dev
```
This starts Laravel server, queue listener, Laravel Pail log stream, and Vite in parallel.

### 13.3 Production Build
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 14. Test Strategy
PHPUnit configuration:
- `phpunit.xml`

Test suites:
- `tests/Unit`
- `tests/Feature`

Default test DB:
- In-memory SQLite (`DB_DATABASE=:memory:`)

Run tests:
```bash
composer run test
```

## 15. Deployment Guide
### 15.1 Recommended Runtime
- Linux server (Ubuntu 22.04 or 24.04 LTS)
- Nginx + PHP-FPM
- MySQL 8+ (or another supported Laravel DB backend)
- TLS (for example Let's Encrypt)

### 15.2 Deployment Flow
1. Pull latest release.
2. Install PHP dependencies (`composer install --no-dev --optimize-autoloader`).
3. Install/build frontend assets (`npm ci && npm run build`).
4. Run migrations.
5. Cache configs/routes/views.
6. Ensure writable permissions on `storage` and `bootstrap/cache`.
7. Restart PHP-FPM and any queue workers.

### 15.3 Required Cron
```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 16. Security Notes
- Enforce HTTPS in production.
- Use strong `APP_KEY` and non-default credentials.
- Restrict administrative accounts and role assignments.
- Validate and sanitize upload sources.
- Keep framework and dependencies patched.
- Restrict file permissions and backup file access.
- Move `GET /optimize-clear` behind admin/auth or remove in production.

## 17. Performance Notes
- Use Laravel production caches (`config:cache`, `route:cache`, `view:cache`).
- Offload large media and backup retention to external object storage as growth increases.
- Monitor DB query performance as dataset volume grows.
- Move long-running tasks to queued jobs where appropriate.

## 18. Operations and Monitoring
Monitor:
- HTTP uptime and latency
- CPU/RAM/disk utilization
- Database health and storage growth
- Queue worker status
- Backup job status and backup freshness
- Laravel logs in `storage/logs`

## 19. Troubleshooting Guide
### 19.1 Common Issues
Missing `APP_KEY`:
- `php artisan key:generate`

Storage links/images not loading:
- `php artisan storage:link`

Build assets missing:
- `npm run build`

Schedule not running:
- Verify cron and `php artisan schedule:list`

Manifest/icon stale on mobile:
- Reinstall app shortcut and clear browser cache

PDF export fails:
- Verify Chrome/Chromium binary availability or configure `REPORTS_PDF_CHROME_BINARY`

### 19.2 Useful Commands
```bash
php artisan route:list
php artisan migrate:status
php artisan optimize:clear
php artisan schedule:list
php artisan backup:auto
```

## 20. Command Reference
Custom commands in repository:

`backup:auto`
- Generates automatic DB snapshot backup according to settings

`museum:import-xlsx {path} {--download-images}`
- Imports museum Excel workbook format into artists/artworks/locations
- Optionally downloads and optimizes first image URL

## 21. Known Gaps and Future Improvements
- Add official restore-from-backup workflow and UI with validations.
- Add test coverage for backup generation/deletion and report export edge cases.
- Restrict or remove public maintenance endpoint (`/optimize-clear`).
- Add formal ERD and sequence diagrams.
- Consider asynchronous export generation for large datasets.

## 22. Source Reference Index
Routes:
- `routes/web.php`
- `routes/console.php`

Controllers:
- `app/Http/Controllers/*.php`

Models:
- `app/Models/*.php`

Services:
- `app/Services/ImageOptimizer.php`

Console Commands:
- `app/Console/Commands/AutoBackupCommand.php`
- `app/Console/Commands/ImportMuseumXlsxCommand.php`

PWA files:
- `public/manifest.json`
- `public/sw.js`

Bootstrap and middleware:
- `bootstrap/app.php`
- `app/Http/Middleware/AdminOnly.php`

---

## Appendix A: Route Access Matrix (Operational Quick Reference)
- Public: `GET /optimize-clear`, `GET /manifest.json`
- Guest-only: login routes
- Authenticated: dashboard, artworks, movements, locations index/show, artists, reports, settings routes
- Admin-only: location create/store/edit/update, admin user CRUD routes, backup generate/download/delete actions (controller-level checks)

## Appendix B: Backup Payload Contract
Example high-level structure:
```json
{
  "generated_at": "2026-03-10T03:00:00+08:00",
  "timezone": "Asia/Kuala_Lumpur",
  "connection": "mysql",
  "tables": {
    "users": [
      {
        "id": 1,
        "name": "..."
      }
    ]
  }
}
```

## Appendix C: Implementation Notes
- Export endpoint path named `reports/export/excel` emits CSV stream (`text/csv`).
- Artwork and report PDF export prefer Chrome headless rendering when executable is available; fallback is DomPDF.
- Settings and backup control are implemented in a single `SettingController` with per-section update handling.

End of technical documentation.
