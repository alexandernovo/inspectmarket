# E-Inspect: Pandan Public Market

Laravel 12 implementation of the supplied Pandan Public Market wireframes. The system has one public homepage and five protected user portals:

- Administrator
- Treasurer
- Clerk
- Inspector
- Tenant

## Local setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`.

All seeded accounts use the password `password`:

| Portal | Username |
| --- | --- |
| Administrator | `administrator` |
| Treasurer | `treasurer` |
| Clerk | `clerk` |
| Inspector | `inspector` |
| Tenant | `tenant` |

## Implemented workflows

- Public homepage, market information, announcements, contact form, service links, and five role portals
- Phone-code registration, login, password reset, profile management, notifications, and direct messages
- Tenant stall applications with document uploads, payment proof uploads, stall map, and livestock inspection requests
- Inspector request review, examination findings, status updates, and printable inspection certificates
- Clerk cash-ticket assignment processing, remittance ranges, shortages, records, and stall-rental monitoring
- Treasurer stall review/assignment, payment verification, official receipts, collector ticket assignments, and announcements
- Administrator member management, stall configuration, contact inbox, settings, reports, CSV exports, and complete oversight
- Searchable/paginated tables, record detail screens, attachment downloads, and role-based authorization

Registration and password-recovery codes are displayed on the verification screen in local development. Replace that delivery step with an SMS provider when production credentials are available. PDF-style outputs use browser print/save-to-PDF so the project does not require a proprietary PDF package.

## Debugging map

- Routes: `routes/web.php`
- Role access middleware: `app/Http/Middleware/EnsureMarketRole.php`
- Role controllers: `app/Http/Controllers/*Controller.php`
- Domain models: `app/Models`
- Market schema: `database/migrations/2026_07_24_000000_create_market_management_tables.php`
- Workflow extensions: `database/migrations/2026_07_24_000001_complete_einspect_workflows.php`
- Demo records: `database/seeders/DatabaseSeeder.php`
- Blade pages: `resources/views/market`
- Styles: `public/assets/einspect/css/market.css`
- Supplied wireframe images: `public/assets/einspect`
- Slide-by-slide mapping of all 285 wireframes: `docs/wireframe-coverage.md`

The main shared domain models are `User`, `Stall`, `StallApplication`, `StallApplicationDocument`, `Payment`, `LivestockInspection`, `CashTicketAssignment`, `CashTicketCollection`, `Announcement`, `MarketMessage`, `MarketNotification`, `ContactMessage`, `SystemSetting`, and `VerificationCode`.

Keeping shared records in shared tables avoids five copies of the same data. Each role controller provides only the queries and actions that role is allowed to use.

## Validation

```powershell
php artisan route:list --except-vendor
php artisan view:cache
php artisan test
vendor\bin\pint --test app bootstrap database routes tests
```
