# Copilot Instructions for Letterhead Inventory Management System

## Project Overview
- **Framework:** Laravel (PHP) with Filament for admin UI
- **Purpose:** Manage letterhead inventory, templates, approval workflows, print tracking, and scan review
- **Key Domains:** Inventory, Templates, Approvals, Print Requests/Results, Scans, Reviews

## Architecture & Patterns
- **Models:** Located in `app/Models/` (e.g., `LetterheadInventory`, `LetterheadTemplate`, `PrintRequest`)
- **Filament Resources:** UI logic in `app/Filament/Resources/` (CRUD, forms, tables)
- **Controllers:** API and web logic in `app/Http/Controllers/`
- **Migrations:** DB schema in `database/migrations/`
- **Seeders:** Initial/test data in `database/seeders/`
- **Routes:** Web/API endpoints in `routes/web.php` and `routes/api.php` (if present)
- **Approval Workflow:** Multi-level, tracked in `template_approvals` table and model
- **Print Flow:** Print requests (`PrintRequest`), items, results, and scan uploads are linked by foreign keys

## Developer Workflows
- **Install:** `composer install` & `npm install`
- **Setup:** Copy `.env.example` to `.env`, run `php artisan key:generate`
- **Migrate/Seed:** `php artisan migrate`, `php artisan db:seed --class=UserSeeder`
- **Build Assets:** `npm run build`
- **Run App:** `php artisan serve`
- **Testing:** Use `php artisan test` or `vendor/bin/phpunit`

## Project-Specific Conventions
- **User Roles:** Admin, Manager, Staff (see README for default logins)
- **Status Fields:** Most core models use status enums/strings (e.g., draft, pending, approved, rejected)
- **File Uploads:** Templates and scans support PDF/image uploads; see model and resource for validation rules
- **Approval Comments:** Comments are stored with approvals and reviews for traceability
- **Serial Number Tracking:** Print requests and results track serial number ranges for auditability

## Integration & Dependencies
- **Filament:** Used for admin panel UI (forms, tables, widgets)
- **Laravel:** Standard service providers, middleware, and Eloquent ORM
- **API:** RESTful endpoints for all major resources (see controllers and routes)
- **Notifications:** Extendable for custom workflow events (not enabled by default)

## Examples & References
- **Inventory CRUD:** `app/Filament/Resources/LetterheadInventoryResource.php`
- **Approval Logic:** `app/Models/TemplateApproval.php`, `app/Filament/Resources/LetterheadTemplateResource.php`
- **Print Request Flow:** `app/Models/PrintRequest.php`, `app/Models/PrintRequestItem.php`, `app/Models/PrintResult.php`
- **Scan Review:** `app/Models/ScanReview.php`, `app/Filament/Resources/ScanReviewResource.php`

## Tips for AI Agents
- Always update both model and resource/form when adding new fields
- Use migrations for DB changes, seeders for test data
- Follow Filament resource patterns for CRUD and UI logic
- Reference README.md for setup, workflows, and troubleshooting
- Use status fields and relationships to drive workflow logic

---
For more details, see the README.md and code comments in key files.