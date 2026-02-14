# Project Structure

Complete folder and file organization of the PRCS Training Management System.

---

## Root Directory Layout

```
train/
├── app/                          # Application source code
├── bootstrap/                    # Framework bootstrap files
├── config/                       # Configuration files
├── database/                     # Migrations, factories, seeders
├── public/                       # Public-facing assets and entry point
├── resources/                    # Frontend assets, templates, translations
├── routes/                       # Route definitions
├── storage/                      # Runtime storage (logs, uploads, backups, cache)
├── tests/                        # Test suites (unit, feature)
├── vendor/                       # Composer dependencies
├── .env                          # Environment variables (not in repo)
├── .gitignore                    # Git ignore rules
├── artisan                       # Laravel CLI entry point
├── composer.json                 # PHP dependencies manifest
├── composer.lock                 # Locked dependency versions
├── package.json                  # JavaScript dependencies
├── phpunit.xml                   # PHPUnit test configuration
├── vite.config.js                # Vite build configuration
└── README.md                     # Project overview (public-facing)
```

---

## Application Structure (`app/`)

### `app/Models/`
Eloquent ORM models with business logic and relationships.

```
Models/
├── Administrative.php            # Governorates/regions
├── Application.php               # Training applications
├── College.php                   # College entities
├── CollegeMajor.php              # Pivot: college ↔ major (many-to-many)
├── Department.php                # Department entities
├── Governorate.php               # Geographic divisions (if separate from Administrative)
├── Institution.php               # University/center level
├── Major.php                     # Academic majors
├── Section.php                   # Training sections (sub-department)
├── Trainee.php                   # Trainee/applicant records
└── User.php                      # System users and administrators
```

**Key Files:**
- Each model file contains relations (hasMany, belongsTo, belongsToMany)
- Each model includes scopes for filtering (e.g., `scopeActive()`)
- Timestamps and soft deletes configured where applicable

### `app/Http/`
HTTP layer: controllers, middleware, form requests.

```
Http/
├── Controllers/
│   ├── ApplicationFormController.php    # API endpoints for form population & submission
│   ├── DownloadAbsorptionPaperController.php # Document download handler
│   └── Controller.php                   # Base controller class
├── Middleware/
│   ├── VerifyCsrfForApi.php             # CSRF verification for API routes
│   ├── Authenticate.php                 # Authentication middleware
│   └── ...                              # Other middleware (throttle, roles, etc.)
└── Requests/
    ├── StoreApplicationRequest.php      # Form request validation
    └── ...                              # Other form requests
```

### `app/Livewire/`
Interactive Livewire components for front-facing forms.

```
Livewire/
├── Welcome/
│   └── WelcomeForm.php                  # Entry/welcome component
└── Trainee/
    ├── TraineeForm.php                  # Main multi-step application form
    ├── DateOfBirthPicker.php            # Date picker sub-component
    ├── Concerns/                        # Trait files for form logic
    │   ├── HasPersonalDetails.php       # Personal info collection
    │   ├── HasEducationDetails.php      # Education/institution selection
    │   ├── HasTrainingPreference.php    # Training type selection
    │   └── ...
    ├── Config/                          # Form configuration
    └── *.css                            # Component styling (CSS modules)
```

### `app/Filament/`
Admin dashboard resources, pages, and widgets built with Filament.

```
Filament/
├── Resources/
│   ├── Institutions/
│   │   ├── InstitutionResource.php      # CRUD interface
│   │   └── Pages/                       # Custom list, create, edit pages
│   ├── Colleges/
│   ├── Departments/
│   ├── Sections/
│   ├── Trainees/
│   ├── Applications/
│   ├── Users/
│   ├── Administratives/
│   └── Stats/                           # Statistics/dashboard resource
├── Pages/
│   ├── Dashboard.php                    # Admin dashboard
│   ├── Settings.php                     # System configuration
│   └── ...
├── Widgets/
│   ├── ApplicationCountWidget.php       # Status counts chart
│   ├── TraineeEnrollmentChart.php       # Enrollment trends
│   └── ...
├── Exporters/
│   ├── ExportApplications.php           # CSV/Excel/PDF export
│   ├── ExportTrainees.php
│   └── ...
└── Auth/
    ├── Login.php                        # Custom login page (if customized)
    └── ...
```

### `app/Console/`
Console commands for CLI operations.

```
Console/
├── Kernel.php                           # Command scheduling & configuration
└── Commands/
    ├── DatabaseBackupCommand.php        # Generate SQL backups
    ├── CleanOldBackupsCommand.php       # Remove old backup files
    ├── EndTrainingCommand.php           # Mark trainings as ended
    └── generate_test_data.php           # Populate test data
```

### `app/Actions/`
Single-responsibility classes for specific domain operations.

```
Actions/
└── Application/
    ├── CreateApplicationAction.php      # Submit new application
    ├── ApproveApplicationAction.php     # Approve application
    ├── RejectApplicationAction.php      # Reject application
    └── ...
```

### `app/Services/`
Business logic and utility services.

```
Services/
├── Application/
│   ├── ApplicationService.php           # Application lifecycle
│   ├── CapacityService.php              # Capacity checking & allocation
│   ├── InterviewService.php             # Interview scheduling
│   └── ...
└── File/
    ├── FileService.php                  # File management
    ├── DocumentService.php              # Document generation
    └── ...
```

### `app/Exports/`
Data export and generation utilities.

```
Exports/
├── BackupExport.php                     # Database backup generation
├── ApplicationExport.php                 # Application CSV/Excel export
└── ...
```

### `app/Policies/`
Authorization policies for model access control.

```
Policies/
├── ApplicationPolicy.php                # Application permissions
├── TraineePolicy.php                    # Trainee permissions
├── UserPolicy.php                       # User management permissions
└── ...
```

### `app/Notifications/`
Notification classes for user alerts.

```
Notifications/
├── ApplicationCreatedNotification.php    # App created alert
├── ApplicationConfirmedNotification.php  # App confirmed alert
├── InitialApprovalNotification.php      # Approval decision
├── StatusUpdateNotification.php          # Status change alert
└── ...
```

### `app/Enums/`
Type-safe enumerations for domain concepts.

```
Enums/
├── ApplicationStatus.php                # Status constants & labels
└── TrainingType.php                     # Training type options
```

### `app/Rules/`
Custom validation rules.

```
Rules/
├── UniqueBySectionAndTrainingType.php   # Custom validation
├── NationalIdFormat.php                 # ID format validation
├── SectionCapacity.php                  # Capacity validation
└── ...
```

### `app/Settings/`
Filament settings models for system configuration.

```
Settings/
└── TrainingSettings.php                 # Form enable/disable, capacity limits, etc.
```

### `app/Helpers/`
Utility helper functions.

```
Helpers/
├── Constants.php                        # Application constants
└── General.php                          # General utilities
```

### `app/Providers/`
Service providers for application bootstrapping.

```
Providers/
├── AppServiceProvider.php               # Application services
├── RouteServiceProvider.php             # Route bindings
├── EventServiceProvider.php             # Event listeners
├── AuthServiceProvider.php              # Authorization policies
└── ...
```

### `app/Support/`
Application-specific support classes and macros.

```
Support/
└── ...                                  # Custom helpers, traits, utilities
```

---

## Configuration (`config/`)

```
config/
├── app.php                              # App name, timezone, providers
├── auth.php                             # Authentication guards and providers
├── backup.php                           # Backup configuration (Spatie)
├── cache.php                            # Cache drivers and settings
├── database.php                         # Database connections
├── debugbar.php                         # Laravel Debugbar (dev only)
├── filesystems.php                      # Storage disks (local, s3, etc.)
├── logging.php                          # Log channels and handlers
├── mail.php                             # Mail driver (smtp, ses, etc.)
├── media-library.php                    # Media library configuration
├── pdf.php                              # PDF generation settings
├── queue.php                            # Queue driver (database, redis, etc.)
├── services.php                         # Third-party service credentials
├── session.php                          # Session configuration
└── ...                                  # Other packages' configs
```

**Key Configs:**
- `app.php` — Set APP_NAME, APP_URL, timezone
- `database.php` — MySQL connection details
- `auth.php` — Guard and provider configuration
- `mail.php` — Email configuration (SMTP, etc.)
- `filesystems.php` — Storage disk definitions

---

## Database (`database/`)

```
database/
├── migrations/                          # Schema migration files
│   ├── *_create_trainees_table.php
│   ├── *_create_applications_table.php
│   ├── *_create_sections_table.php
│   └── ...
├── seeders/
│   ├── DatabaseSeeder.php               # Main seeder (calls others)
│   ├── InstitutionSeeder.php            # Seed institutions
│   ├── TraineeSeeder.php                # Seed sample trainees
│   └── ...
└── factories/
    ├── TraineeFactory.php               # Trainee factory
    ├── ApplicationFactory.php           # Application factory
    └── ...                              # Model factories for testing
```

---

## Resources (`resources/`)

Frontend assets and Blade templates.

```
resources/
├── css/
│   ├── app.css                          # Main stylesheet
│   └── ...
├── js/
│   ├── app.js                           # Main JavaScript entry
│   └── ...
├── views/
│   ├── Form/
│   │   ├── welcomeapp.blade.php         # Welcome page (fallback)
│   │   └── trainee-app/
│   │       └── index.blade.php          # Form page (fallback)
│   ├── layouts/                         # Layout templates
│   ├── components/                      # Blade components
│   └── ...
└── lang/
    ├── en/
    ├── ar/                              # Arabic translations
    └── ...
```

---

## Routes (`routes/`)

```
routes/
├── web.php                              # Web routes (forms, public, auth)
│   ├── GET /                            # Root redirect
│   ├── GET /welcome                     # Welcome form
│   ├── GET /welcome/form                # Trainee form
│   ├── GET /welcome/form/api/*          # API endpoints
│   ├── GET /applications/:id/...        # Download endpoints
│   └── Admin routes (Filament)
├── api.php                              # REST API routes (if applicable)
└── console.php                          # Console route bindings
```

**Route Groups:**
- Public routes (form, welcome)
- API routes (throttled, CSRF-protected)
- Admin routes (Filament, authenticated)

---

## Storage (`storage/`)

Runtime storage for logs, caches, uploads, and backups.

```
storage/
├── app/
│   └── private/
│       └── backups/                     # Database backup SQL files
│           └── backup_*.sql             # Timestamped backup files
├── framework/
│   ├── cache/                           # Application cache
│   ├── views/                           # Compiled Blade views
│   └── ...
├── logs/
│   ├── laravel.log                      # Main application log
│   └── ...
└── debugbar/                            # Debugbar cache (dev only)
```

---

## Tests (`tests/`)

```
tests/
├── Feature/
│   ├── ApplicationFormTest.php          # Test form endpoints
│   ├── ApplicationControllerTest.php    # Test controller actions
│   └── ...
├── Unit/
│   ├── Models/
│   │   ├── ApplicationTest.php          # Test model logic
│   │   ├── TraineeTest.php
│   │   └── ...
│   ├── Services/
│   │   ├── CapacityServiceTest.php
│   │   └── ...
│   └── ...
├── TestCase.php                         # Base test class
└── phpunit.xml                          # PHPUnit configuration
```

---

## Key Files at Root

- **artisan** — Laravel CLI entry point; run commands
- **composer.json** — PHP dependency definitions
- **package.json** — JavaScript (npm) dependencies
- **vite.config.js** — Frontend build tool configuration
- **phpunit.xml** — Unit test configuration
- **.env** — Environment variables (local only, not committed)
- **.env.example** — Example environment template
- **.gitignore** — Files to exclude from Git

---

## Special Directories

### `app/Package notes/`
Internal documentation (this folder):
- `README.md` — Project overview (copy of public README)
- `Component Structure.md` — UI components & resources
- `Implementation Notes.md` — Design patterns, services, commands
- `Project Structure.md` — This file

---

## File Naming Conventions

- **Models** — Singular, PascalCase: `Trainee.php`, `Application.php`
- **Controllers** — PascalCase with `Controller` suffix: `ApplicationFormController.php`
- **Migrations** — Timestamp + snake_case: `2024_01_22_100000_create_trainees_table.php`
- **Seeders** — Singular, PascalCase: `TraineeSeeder.php`
- **Policies** — Model name + `Policy`: `ApplicationPolicy.php`
- **Commands** — PascalCase: `DatabaseBackupCommand.php`
- **Tests** — Model/class name + `Test`: `ApplicationTest.php`
- **Blade views** — snake_case, `.blade.php`: `welcome_form.blade.php`
- **Livewire components** — PascalCase: `TraineeForm.php`
- **CSS/JS** — kebab-case for static files: `form-footer.css`

---

## Database Schema Overview

```
trainees
├── id (PK)
├── name
├── email
├── phone
├── national_id (unique)
├── date_of_birth
├── section_id (FK) [optional]
├── administrative_id (FK)
└── timestamps

applications
├── id (PK)
├── trainee_id (FK)
├── institution_id (FK)
├── college_id (FK)
├── department_id (FK)
├── section_id (FK)
├── major_id (FK)
├── status (enum)
├── training_type
├── start_date
├── end_date
└── timestamps

organizations (hierarchy)
├── institutions
│   └── colleges
│       └── departments
│           └── sections

administrative (geography)
└── governorates/regions
```

---

## Deployment Structure

For production, consider:
- Move `storage/` and `bootstrap/cache/` outside web root
- Symlink public directory as web root
- Keep `.env` and sensitive files outside repo
- Run migrations with zero-downtime via CI/CD pipeline
- Archive backups separately (not in repo storage)
