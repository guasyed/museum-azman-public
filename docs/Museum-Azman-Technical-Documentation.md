# Museum Azman Technical Documentation

Version: 1.3  
Date: 2026-05-12  
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
- CSV/XLSX import workflows with optional image download
- User approval, role management, notification preferences, and activity logs
- Configurable statuses, regional settings, timezone management, and backup operations
- Installable PWA behavior for mobile shortcut/app mode

## 3. Technology Stack
### 3.1 Backend
- PHP: `>=8.2 <8.6`
- Framework: `laravel/framework ^12.0`
- Key packages:
- `laravel/tinker`
- `barryvdh/laravel-dompdf` / `dompdf/dompdf` (runtime PDF fallback currently present in `vendor/`)
- `bupple/laravel-ai-engine` (optional, environment-configurable)

### 3.2 Frontend
- Blade templates (`resources/views`)
- Vite build pipeline
- Tailwind CSS 4
- Vanilla JavaScript for interactive features:
- AJAX flows (including JSON create/update responses)
- Modal interactions
- Search suggestions/autocomplete
- Notification dropdown and install prompt behavior
- PWA/service worker behavior

### 3.3 Data and Storage
- Primary database: configurable via Laravel (`sqlite` default in `.env.example`), production typically MySQL
- Backup snapshot logic supports table extraction for `mysql`, `pgsql`, and `sqlite`
- File storage:
- `public` disk for images/assets (`storage/app/public`)
- `local` disk for private JSON backup snapshots (`storage/app/private/backups`)
- Browser security headers are applied by `SetContentSecurityPolicy`

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
7. Response rendering as Blade HTML, JSON, streamed CSV, PDF download, file download, or redirect.

### 5.2 Scheduled Operations Architecture
1. OS cron executes `php artisan schedule:run` every minute.
2. Laravel scheduler in `routes/console.php` resolves backup time and timezone from `settings` table.
3. `backup:auto` runs daily using configured time and timezone.
4. Backup file is written under `storage/app/private/backups`.

### 5.3 Layered Design
- Presentation layer: Blade templates and JS UI logic
- Application layer: controllers and form requests (`app/Http/Requests`)
- Domain layer: Eloquent models and relationships
- Service layer: reusable operation services (`ImageOptimizer`, `ActivityLogger`)
- Infrastructure layer: route config, scheduler, filesystem, database and environment config

## 6. Core Modules
### 6.1 Authentication Module
- Controller: `app/Http/Controllers/AuthController.php`
- Guest routes:
- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `GET /forgot-password`
- `POST /forgot-password`
- `GET /reset-password/{token}`
- `POST /reset-password`
- Auth route:
- `POST /logout`
- Registration excludes `owner` and `admin` role requests, creates users as unapproved, and notifies approved admins.
- Login blocks users whose `is_approved` flag is false.
- Auth actions are recorded in `activity_logs`.

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
  'total_artists' => $artworks->pluck('artist_id')->filter()->unique()->count(),
  'total_locations' => $artworks->pluck('location_id')->filter()->unique()->count(),
  'collection_value' => (float) $artworks->sum('current_valuation'),
  'in_stage' => $artworks->where('status', 'In Stage')->count(),
  'on_loan' => $artworks->where('status', 'On Loan')->count(),
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
- `GET /movements/{movement}/edit`
- `PUT /movements/{movement}`
- `DELETE /movements/{movement}`
- Tracks movement and syncs linked artwork status/location from the latest movement.
- Logistics-handler users see only movements assigned to their exact `responsible_handler` name.
- Admins can create/edit/delete any movement; assigned logistics handlers can edit/delete their own assigned movements but cannot create new movements.
- Assignment changes notify the responsible handler when notification preferences allow it.
- Handler-originated updates notify approved admins.

Sample code:
```php
// app/Http/Controllers/MovementController.php (store excerpt)
$movement = Movement::create($request->validated());

$this->notifyResponsibleHandlerAssignment($movement);
$this->syncArtworkStatus((int) $movement->artwork_id);

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
- `POST /settings/statuses`
- `POST /settings/statuses/{status}/toggle`
- `DELETE /settings/statuses/{status}`
- `POST /settings/backup/generate`
- `GET /settings/backup/download`
- `POST /settings/backup/delete`
- Features organization profile, regional settings, user notification preferences, appearance settings, configurable artwork/movement statuses, timezone-aware backup metadata, backup listing, and key-value settings persistence.
- Admin-only settings sections: general, regional, backup, statuses, users/roles.
- User-accessible settings sections: notifications and appearance.
- Notification preferences are stored on the current user; admins also persist defaults in the `settings` table.
- Appearance settings support light/dark theme, density, accent color, heading font, and body font.
- Saving settings clears the cached currency/date-format support helpers.

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
- `Admin Users` (`/admin/users`): `name`, `email`, `role`, `status`
- `Settings > Users & Roles` (`/settings?tab=users-roles`): `name`, `email`, `role`, `status`
- `Collection` table view (`/artworks?view=table`): `title`, `current_valuation`, `created_at` (default)
- `Locations` list view (`/locations?view=list`): `name`, `type`
- `Movement History` (`/movements`): `artwork_title`, `from_location`, `to_location`, `date_out`, `expected_return_date`, `responsible_handler`, `reason`, `status`

Sorting conventions:
- Query parameters: `sort` and `direction` (`asc` or `desc`)
- Settings users table uses `user_sort` and `user_direction`
- Active sort indicator: arrow marker in header (`▲` or `▼`)
- Existing filters and view mode are preserved when changing sort order
- Related model sort fields (for example role/artwork title) are handled via controlled joins with `select(base_table.*)`

### 6.11 Import and Activity Modules
- CSV import UI: `GET/POST /admin/imports/csv`
- CSV command: `museum:import-csv {path} {--connection=} {--download-images} {--skip-images} {--fresh}`
- XLSX command: `museum:import-xlsx {path} {--download-images}`
- Activity log UI: `GET /admin/activity-logs`
- Activity logging service: `app/Services/ActivityLogger.php`
- Activity logs capture action, description, authenticated user, IP address, user agent, optional subject type/id/label, and can be searched/filtered by admins.
- Notifications: new-user registration and movement assignment/update notifications are stored in the Laravel notifications table when available.

### 6.12 Profile Module
- Controller: `app/Http/Controllers/ProfileController.php`
- Routes:
- `GET /profile`
- `PUT /profile`
- `PUT /profile/password`
- Users can update profile name/avatar; only admins can update their own email address from the profile form.
- Password updates require the current password and reject reusing the current password.
- Profile and password changes are logged through `ActivityLogger`.

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
- `Country` normalizes artist country references
- `Status` stores active/default status options
- `ActivityLog` stores auditable user/admin events
- `Setting` is key-value configuration storage

Notable model behavior:
- `User::isAdmin()` treats both `admin` and `owner` as privileged
- `User::isLogisticsHandler()` recognizes the `logistics-handler` role slug and legacy handler role labels
- `User::isApproved()` defaults legacy/null approval state to approved
- `Artwork` exposes computed `primary_image_url`
- `ArtworkImage` exposes computed `url`
- `User` exposes computed `avatar_url`

## 8. Routing and Access Control
Routes files:
- `routes/web.php`
- `routes/console.php`

Access boundaries:
- `guest` middleware:
- login, registration, forgot password, and reset password routes
- `auth` middleware:
- main application routes
- `admin` middleware (`App\Http\Middleware\AdminOnly`):
- privileged mutating routes and admin user management
- logistics-handler behavior is enforced inside `MovementController` for assigned-only movement access

Middleware wiring:
- Alias configured in `bootstrap/app.php`
- Proxy trust configured with `trustProxies('*')` for tunnel/reverse proxy compatibility

PWA manifest route:
- `GET /manifest.json` returns `public/manifest.json` via file response

Operational endpoint note:
- `GET /optimize-clear` is protected by `auth` and `admin` middleware and clears Laravel optimize/cache/config/route/view caches.
- `GET /admin/technical-documentation` serves `docs/Museum-Azman-Technical-Documentation.html` with `X-Robots-Tag: noindex, nofollow`.

## 9. File and Image Handling
Service: `app/Services/ImageOptimizer.php`

Capabilities:
- Store uploaded image from `UploadedFile`
- Store image from URL via HTTP client (`--download-images` import option)
- Resize/compress image via GD to max side 1800px
- Save optimized output as WebP or JPEG
- Fallback to original binary storage for unsupported transforms
- Store gallery images in `artwork_images`; the first valid imported image can become `primary_image_path`

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
- Layout head includes Apple mobile web app metadata and icon links.
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
- PDF Chrome override:
- `REPORTS_PDF_CHROME_BINARY`

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
7. Ensure `php artisan storage:link` exists for uploaded public media.
8. Restart PHP-FPM and any queue workers.

### 15.3 Required Cron
```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 16. Security Notes
- Enforce HTTPS in production.
- Use strong `APP_KEY` and non-default credentials.
- Restrict administrative accounts and role assignments.
- Keep registration approval enabled for non-admin/owner role requests.
- Validate and sanitize upload sources.
- Keep framework and dependencies patched.
- Restrict file permissions and backup file access.
- Keep maintenance and documentation routes behind admin/auth boundaries.
- Verify `composer.json` and `composer.lock` are aligned before deployment.

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
- Failed notification/email delivery
- Pending user registrations awaiting approval
- Movement assignments with responsible-handler names that do not match an approved logistics-handler user
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
- Verify DomPDF dependencies are committed in Composer metadata if relying on fallback rendering

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

`museum:import-csv {path} {--connection=} {--download-images} {--skip-images} {--fresh}`
- Imports CSV artwork data, supports optional alternate DB connection, optional image download, and a fresh artwork import mode

## 21. Known Gaps and Future Improvements
- Add official restore-from-backup workflow and UI with validations.
- Add test coverage for backup generation/deletion and report export edge cases.
- Add formal ERD and sequence diagrams.
- Consider asynchronous export generation for large datasets.
- Align Composer manifests with runtime PDF fallback dependencies before a clean production install.

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
- `app/Services/ActivityLogger.php`

Console Commands:
- `app/Console/Commands/AutoBackupCommand.php`
- `app/Console/Commands/ImportMuseumXlsxCommand.php`
- `app/Console/Commands/ImportMuseumCsvCommand.php`

PWA files:
- `public/manifest.json`
- `public/sw.js`

Bootstrap and middleware:
- `bootstrap/app.php`
- `app/Http/Middleware/AdminOnly.php`

---

## Appendix A: Route Access Matrix (Operational Quick Reference)
- Public: `GET /manifest.json`, public storage proxy route for validated `storage/*` paths
- Guest-only: login, registration, forgot-password, reset-password routes
- Authenticated: dashboard, profile, notification mark-read, artworks, movements, locations index/show, artists, reports, settings routes
- Admin-only: `GET /optimize-clear`, location create/store/edit/update, artist create/update/delete, admin imports, admin technical documentation, admin activity logs, admin user CRUD/approval routes, backup generate/download/delete actions, status management actions

## Appendix B: Backup Payload Contract
Example high-level structure:
```json
{
  "generated_at": "2026-05-12T03:00:00+08:00",
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
