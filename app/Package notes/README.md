# PRCS Training Management System

A Laravel + Filament application for managing student training requests, department capacity, interviews, and trainee progression.

This repository contains the server-side codebase for the PRCS training management application. It implements:

- Trainee application workflows and forms (Livewire)
- Administrative dashboards and CRUD resources (Filament)
- Data models for institutions, majors, departments, sections, trainees, and applications
- Server-side exports and utilities (including an internal PHP-based DB exporter used by administrators)

This project is private. The README intentionally provides a high-level description only; operational, deployment, and access details are managed internally.

## System Requirements

- PHP 8.1+ (8.3 recommended)
- Composer
- MySQL 5.7+ or compatible
- Node.js (for frontend tooling, optional for server-only deployments)

## Languages & Frameworks

- PHP (application server, business logic)
- Laravel (framework)
- Livewire (interactive components)
- Filament (admin UI)
- SQL (MySQL dialect)
- JavaScript, HTML, CSS (frontend assets)

## Architecture Overview

This application follows a typical Laravel server-side architecture with additional UI provided by Filament and interactive form flows implemented with Livewire. Core responsibilities are separated as:

- HTTP layer: controllers, route definitions and middleware
- UI layer: Filament resources for admin pages and Livewire components for interactive forms
- Domain layer: Eloquent models, policies, services and actions
- Persistence: MySQL (via Eloquent) and filesystem storage for exports
- Background jobs and scheduled tasks: queued jobs and Artisan console commands

## Key Directories

- `app/Models` — Eloquent models (trainees, applications, institutions, majors, etc.)
- `app/Http/Controllers` — Controllers and API endpoints
- `app/Livewire` — Livewire components and form flows
- `app/Console/Commands` — Custom Artisan commands (backup, cleanup, etc.)
- `app/Exports` — Export utilities (DB export, CSV/Excel exporters)
- `resources/views` — Blade templates
- `routes` — Route definitions (`web.php`, `api.php`, `console.php`)
- `config` — Application configuration
- `storage/app/private/backups` — Backup storage (internal SQL dump files)

## Data Model (high level)

Primary entities include:

- `Trainee` — represents an applicant or trainee
- `Application` — training application submitted by a trainee
- `Institution`, `College`, `Department`, `Major`, `Section` — organizational hierarchy
- `User` — administrative or system users with roles and permissions

Relationships are implemented using Eloquent relations and enforced through policies for access control.

## Key Components

- Livewire components handle step-by-step front-facing application forms and validation.
- Filament resources provide the administrative CRUD UI and lists for managing trainees, applications and organizational data.
- Custom console commands provide operational tooling (PHP-based DB export, cleanup routines) to avoid external binary dependencies on some hosts.
- **Telegram Bot Integration:** A built-in system for real-time monitoring, error logging, and administrative controls via individual Telegram commands or webhooks.

## Backups & Scheduling (concept)

Backups are implemented as a PHP-native exporter that generates SQL dumps and stores them in the private storage disk. Scheduled tasks are defined in the application scheduler (`app/Console/Kernel.php`) so environments can run `schedule:run` as appropriate to execute backups and cleanup on a cadence configured by the team.

## Testing

The project includes PHPUnit tests under `tests/`. Run tests via your CI pipeline or local test runner as configured by your development process.

## Coding Standards & Conventions

- Follow PSR-12 for PHP code formatting.
- Use Eloquent for data access; keep business logic in Actions/Services when appropriate.
- Add PHPDoc blocks for public methods and non-trivial classes.

## Environment Variables (overview)

The application relies on typical Laravel environment variables for configuration, such as `APP_ENV`, `APP_DEBUG`, `APP_URL`, and the `DB_*` connection variables. Do not commit `.env` or secret values to the repository.

## Internal / Contact

This repository is private — operational processes, deployment steps, and sensitive scripts are documented in internal documentation only. For questions about deployment or operations, contact the project admin or ops team.
