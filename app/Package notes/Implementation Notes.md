# Implementation Notes

This document describes the design patterns, service layer, actions, migrations, exports, and custom console commands used throughout the PRCS Training Management System.

---

## Design Patterns

### Model-View-Controller (MVC)

- **Models** (`app/Models/`) — Eloquent models with business logic, relations, and accessors/mutators
- **Views** — Blade templates and Livewire components handle rendering
- **Controllers** — Route handlers, API endpoints, form processing

### Action Classes

Located in `app/Actions/Application/`, these encapsulate specific business logic:

- **Purpose:** Single responsibility, reusable operations
- **Example:** CreateApplicationAction, UpdateApplicationStatusAction
- **Pattern:** Accept parameters, validate, perform action, return result
- **Usage:** Called from controllers, Livewire components, or console commands

### Service Layer

Located in `app/Services/`, services provide domain-specific utilities:

#### Application Services (`app/Services/Application/`)

- Application status transitions
- Capacity checking (availability in departments/sections)
- Interview scheduling
- Trainee assignment logic
- Notifications and email triggers

### User Services (`app/Services/User/`)

- User management logic (if separate from models)

#### Telegram Services (`app/Services/Telegram/`)

- **TelegramMonitorService:** Handles all communication with the Telegram API
    - Error reporting (`Log::error` integration)
    - Activity monitoring (Application created, status updated)
    - Bot command processing (`/status`, `/start`, `/report`)
    - Webhook handling

#### File Services (`app/Services/File/`)

- Generate and manage training documents
- Handle file uploads
- Manage document templates
- PDF generation and storage

#### Import Services (`app/Services/ExcelImportService.php`)

- **ExcelImportService:** Handles bulk trainee data import
    - Uses `PhpSpreadsheet` to parse CSV/Excel files
    - **Transactional Processing:** Ensures Trainee and Application are created together
    - **Conflict Resolution:** Prevents importing students with active applications
    - **Smart Mapping:** Resolves IDs (Major, Governorate) from names
    - **Preview Mode:** Allows users to review mapped data before committing

### Repository Pattern (Optional)

If implemented, repositories abstract data access:

- Query building
- Complex filtering and sorting
- Eager loading strategies
- Caching logic

---

## Eloquent Models & Relations

### Core Models

#### Trainee (`app/Models/Trainee.php`)

**Purpose:** Represents an applicant or training participant.
**Key Fields:** name, email, phone, national_id, date_of_birth, status
**Relations:**

- `hasMany('applications')` — Multiple applications/training sessions
- `belongsTo('section')` — Assigned to section (optional)
- `belongsTo('administrative')` — Governorate/administrative division
  **Methods:**
- Status accessors (isActive, isEnded, etc.)
- Scope methods for filtering (active, inactive, etc.)

#### Application (`app/Models/Application.php`)

**Purpose:** Represents a single training application.
**Key Fields:** status, training_type, start_date, end_date, section_id, notes
**Status Constants:** NEW, INITIAL_APPROVE, CONFIRMATION, WAITING_LIST, STARTED_TRAINING, ENDED_TRAINING, REJECTED, DROPPED
**Relations:**

- `belongsTo('trainee')` — Associated trainee
- `belongsTo('section')` — Assigned section (if approved)
- `belongsTo('major')` — Requested major
- `belongsTo('department')` — Requested department
- `belongsTo('institution')` — Requested institution
- `hasMany('interviews')` — Interview records (if exists)
  **Methods:**
- `canBeApproved()` — Check capacity and eligibility
- `approve()`, `reject()`, `waitlist()` — Status transitions
- `scopeByStatus()` — Filter by status

#### Organization Hierarchy

**Institution** → **College** → **Department** → **Section**

- **Institution** (`app/Models/Institution.php`)
    - Top-level organization (university, center)
    - Capacity and configuration

- **College** (`app/Models/College.php`)
    - Sub-unit of Institution
    - Links to departments and majors

- **Department** (`app/Models/Department.php`)
    - Sub-unit of College
    - Manages capacity per training type and period

- **Section** (`app/Models/Section.php`)
    - Sub-unit of Department
    - Assigns trainees and tracks capacity

- **Major** (`app/Models/Major.php`)
    - Represents academic majors
    - BelongsToMany Colleges (college_major pivot table)

#### Administrative (`app/Models/Administrative.php`)

- Governorates and administrative regions
- Used for trainee location tracking

#### User (`app/Models/User.php`)

- System administrators and staff
- Roles and permissions (if using Laravel authorization)

---

## Database Migrations

Located in `database/migrations/`, migrations define schema and relationships.

### Key Migrations

- `create_trainees_table` — Trainee records
- `create_applications_table` — Application submissions
- `create_sections_table` — Training sections
- `create_institutions_table` — Institution master data
- `create_colleges_table` — College records
- `create_departments_table` — Department records
- `create_majors_table` — Major definitions
- `create_college_major_table` — College↔Major pivot
- `create_users_table` — System users
- `create_administrative_table` — Administrative divisions

### Running Migrations

```bash
php artisan migrate
```

Migrations are applied in order; rollback with `php artisan migrate:rollback`.

---

## Enums

Located in `app/Enums/`, enums provide type-safe constants for domain concepts.

### ApplicationStatus (`app/Enums/ApplicationStatus.php`)

- Maps status constants (NEW, APPROVED, REJECTED, etc.) to labels and descriptions
- Used in models, controllers, and policies

### TrainingType (`app/Enums/TrainingType.php`)

- University training
- Practice/internship training
- On-the-job training (if applicable)

**Usage:** Type hints in methods, form options, database validation.

---

## Policies & Authorization

Located in `app/Policies/`, policies define who can perform actions.

### Key Policies

- **ApplicationPolicy** — Permissions to view, create, update, approve applications
    - `viewAny()` — Can list applications (admin only)
    - `view()` — Can see a specific application (trainee or admin)
    - `create()` — Can create application (public users)
    - `downloadAbsorptionPaper()` — Can download related documents

- **TraineePolicy** — Permissions for trainee records
    - `viewAny()` — Admin only
    - `view()` — Trainee self + admin

### Authorization in Controllers

```php
$this->authorize('downloadAbsorptionPaper', $application);
```

---

## Notifications

Located in `app/Notifications/`, notifications alert users of status changes.

### Notification Types

- **ApplicationCreatedNotification** — When trainee submits application
- **ApplicationConfirmedNotification** — When application is confirmed
- **InitialApprovalNotification** — When initial approval decision made
- **StatusUpdateNotification** — On status changes

**Channels:** Email, database, SMS (if configured)
**Triggered From:** Model observers, service layer, console commands

---

## Exports & File Generation

Located in `app/Exports/`:

### BackupExport (`app/Exports/BackupExport.php`)

- Generates SQL database dump (PHP-native, no mysqldump)
- Methods:
    - `export()` — Stream SQL dump for download
    - `saveToStorage()` — Save to private disk
    - `generateBackupSql()` — Build SQL string
- Used by scheduled backup command

### Filament Exporters (CSV, Excel, PDF)

- ExportApplications
- ExportTrainees
- ExportSections
- Located in `app/Filament/Exporters/`

---

## Console Commands

Located in `app/Console/Commands/`, custom artisan commands for operational tasks.

### DatabaseBackupCommand (`app/Console/Commands/DatabaseBackupCommand.php`)

**Command:** `php artisan app:database-backup`
**Purpose:** Create a full database backup
**Execution:**

- Queries all tables and generates SQL INSERT statements
- Writes to `storage/app/private/backups/` with timestamp
- No external dependencies (no mysqldump required)
  **Scheduled:** Daily at 10:00 AM via `app/Console/Kernel.php`

### CleanOldBackupsCommand (`app/Console/Commands/CleanOldBackupsCommand.php`)

**Command:** `php artisan app:clean-old-backups --days=10`
**Purpose:** Remove old backup files
**Execution:**

- Scans backup directory for files older than N days (default 10)
- Extracts date from filename: `backup_[db]_YYYY-MM-DD_HH-mm-ss.sql`
- Deletes expired files
  **Options:**
- `--days=N` — Set age threshold (default 10 days)
  **Scheduled:** Every 10 days at 10:15 AM via `app/Console/Kernel.php`

### EndTrainingCommand (Optional)

- Transitions active trainings to "ended" status
- Runs daily or on-demand
- Updates related records and notifications

### Generate Test Data Command (Optional)

- Populates database with sample data for development
- Creates fake trainees, applications, sections, etc.

### TelegramListenCommand (`app/Console/Commands/TelegramListenCommand.php`)

**Command:** `php artisan telegram:listen`
**Purpose:** Starts a long-polling process to receive Telegram updates.
**Use Case:** Development environments (Localhost) where Webhooks cannot reach the server.
**Note:** Do not use in production; use Webhooks instead.

---

## Settings & Configuration

### TrainingSettings (`app/Settings/TrainingSettings.php`)

- Filament settings resource for system configuration
- Fields:
    - `is_public_form_enabled` — Enable/disable trainee applications
    - `enable_training_type_university` — Allow university training
    - `enable_training_type_practice` — Allow practice training
    - Capacity limits per department/section
    - Interview date ranges
    - Notification preferences

**Usage:** Injected into controllers and commands via constructor

---

## Database Transactions & Atomicity

For complex operations (e.g., approving applications with capacity checks):

```php
DB::transaction(function () {
    // Update application status
    // Update capacity
    // Send notifications
    // All succeed or all rollback
});
```

---

## Validation Rules

Located in `app/Rules/`, custom validation rules extend Laravel validators.

### Examples

- **UniqueBySectionAndTrainingType** — Ensure no duplicate applications
- **NationalIdFormat** — Validate ID format
- **AgeRange** — Ensure trainee meets age requirements
- **SectionCapacity** — Check available capacity

**Usage in Livewire/Controllers:**

```php
'national_id' => 'required|unique:trainees|' . new NationalIdFormat()
```

---

## Service Provider & Bindings

Located in `app/Providers/`, service providers register application services.

### AppServiceProvider

- Register service bindings
- Bind repositories to contracts (if using)
- Boot observers for models
- Register macro extensions

### RouteServiceProvider

- Define route namespaces
- Register model binding (`{application}` → Application model auto-injection)

---

## Helpers

Located in `app/Helpers/`:

- **General.php** — Common utility functions
- **Constants.php** — Application-wide constants (status labels, form field names, etc.)

---

## Testing

Tests are located in `tests/`:

- **Unit tests** (`tests/Unit/`) — Test models, services, helpers in isolation
- **Feature tests** (`tests/Feature/`) — Test full request-response cycles, controller logic, policies

### Test Database

Tests use an in-memory SQLite database or separate test database. Configure in `phpunit.xml`.

---

## Caching & Performance

### Query Caching

- For heavy queries (org hierarchy, capacity checks), cache results:

```php
Cache::remember("institution:{$id}", now()->addHours(24), fn() => Institution::find($id));
```

### View Caching

- Compiled views cached; clear with `php artisan view:clear`

### Config Caching

- Configuration cached in production; clear with `php artisan config:clear`

---

## Error Handling & Logging

### Exception Handling

- Located in `app/Exceptions/Handler.php`
- Custom exceptions for domain errors
- API responses with appropriate HTTP codes

### Logging

- Configured in `config/logging.php`
- Logs written to `storage/logs/`
- Levels: debug, info, notice, warning, error, critical, alert, emergency

---

## API Endpoints (JSON Responses)

All form API endpoints in `routes/web.php` return JSON:

```json
{
  "data": [
    { "id": 1, "name": "Institution Name" },
    ...
  ]
}
```

These feed form selects and validate inputs on the client side.
