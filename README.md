# Museum Azman

Museum Azman is a Laravel 12 web application for managing artworks, artists, locations, movement tracking, reporting, and operational settings.

## Features

- Artwork CRUD with primary image upload and optimization
- Artist and location management
- Movement tracking and artwork status updates
- Reports export (CSV/PDF)
- Role-based access with `auth` and `admin` middleware
- Manual and scheduled database snapshot backup
- PWA support (`manifest.json` + service worker)

## Technology Stack

- PHP `^8.2` (min)
- Laravel `^12.0`
- Blade templates
- Vite + Tailwind CSS
- SQLite/MySQL/PostgreSQL supported by Laravel config

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

Run development server:

```bash
composer run dev
```

## Production Build

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Testing

```bash
composer run test
```

## Important Commands

- `php artisan backup:auto` - Generate automatic backup snapshot
- `php artisan museum:import-xlsx {path} {--download-images}` - Import museum data
- `php artisan schedule:list` - Verify scheduled tasks

Cron (recommended):

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Backups

- Stored under `storage/app/private/backups`
- Can be generated manually from settings UI or automatically via scheduler

## Project Docs

- `docs/Museum-Azman-Technical-Documentation.md`
- `docs/Museum-Azman-Technical-Documentation.html`

## Security Notes

- Ensure production uses HTTPS and secure credentials
- Restrict admin users and protect backup files
- Review exposed maintenance endpoints before production deployment

## License

This project is distributed under the MIT license.
