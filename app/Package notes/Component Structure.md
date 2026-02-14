# Component Structure

This document describes the UI components, resources, and controllers that make up the PRCS Training Management System.

---

## Livewire Components

Livewire is used for interactive, real-time form components. Components are reactive and handle client-side validation, data binding, and real-time feedback.

### Welcome Component (`app/Livewire/Welcome/WelcomeForm.php`)

**Purpose:** Entry point form for first-time applicants.
**Responsibilities:**
- Display welcome information
- Collect basic trainee information
- Guide users to the full application form
**Routes:** Served at `/welcome`

### Trainee Form Component (`app/Livewire/Trainee/TraineeForm.php`)

**Purpose:** Main multi-step application form for trainee registration.
**Responsibilities:**
- Collect personal details (name, DOB, national ID, phone)
- Collect educational details (institution, college, major, department, section)
- Collect training preferences (training type, preferred date)
- Submit application data to `Application` model
- Handle client-side validation and real-time feedback

**Sub-components/Concerns:**
- `DateOfBirthPicker.php` — Custom date picker for DOB input
- Concerns (in `app/Livewire/Trainee/Concerns/`) — Traits for specific form logic (if applicable)
- Styling: CSS modules for form layout, fieldsets, and footer

**Related API Endpoints:** Calls `/welcome/form/api/*` routes for dynamic select population

---

## Filament Resources (Admin Dashboard)

Filament provides the administrative CRUD interface. Each resource corresponds to a domain entity and offers List, Create, Edit, and Delete actions.

### Organization Hierarchy Resources

#### Institutions Resource (`app/Filament/Resources/Institutions/`)
**Entity:** `App\Models\Institution`
**Purpose:** Manage institutions (universities, centers, etc.)
**CRUD:** Create, list, edit, delete institutions
**Relations:** Parent for Colleges

#### Colleges Resource (`app/Filament/Resources/Colleges/`)
**Entity:** `App\Models\College`
**Purpose:** Manage colleges within institutions
**CRUD:** Create, list, edit, delete colleges
**Relations:** BelongsTo Institution, has many Departments

#### Departments Resource (`app/Filament/Resources/Departments/`)
**Entity:** `App\Models\Department`
**Purpose:** Manage departments within colleges
**CRUD:** Create, list, edit, delete departments
**Relations:** BelongsTo College, has many Sections and Majors

#### Sections Resource (`app/Filament/Resources/Sections/`)
**Entity:** `App\Models\Section`
**Purpose:** Manage sections (subdivisions within departments)
**CRUD:** Create, list, edit, delete sections
**Relations:** BelongsTo Department, has many Trainees

### Application & Trainee Resources

#### Applications Resource (`app/Filament/Resources/Applications/`)
**Entity:** `App\Models\Application`
**Purpose:** Manage trainee applications and their lifecycle
**CRUD:** List, view, and update applications
**Statuses:** 
- New, Initial Approval, Confirmation, Waiting List, Started Training, Ended Training, Rejected, Dropped, Unknown
**Key Actions:** 
- Approve/reject applications
- Export application lists (CSV, Excel, PDF)
- Filter by status, trainee, date range
**Relations:** BelongsTo Trainee, has many supporting records

#### Trainees Resource (`app/Filament/Resources/Trainees/`)
**Entity:** `App\Models\Trainee`
**Purpose:** Manage trainee records
**CRUD:** Create, list, edit, delete trainees
**Key Fields:** Name, email, phone, national ID, status
**Relations:** Has many Applications

### Administrative & Configuration Resources

#### Administratives Resource (`app/Filament/Resources/Administratives/`)
**Entity:** `App\Models\Administrative`
**Purpose:** Manage administrative divisions (governorates, regions)
**CRUD:** Create, list, edit, delete administrative records

#### Users Resource (`app/Filament/Resources/Users/`)
**Entity:** `App\Models\User`
**Purpose:** Manage system users, roles, and permissions
**CRUD:** Create, list, edit, delete users
**Key Features:** Role assignment, permission management

#### Statistics Resource (`app/Filament/Resources/Stats/`)
**Purpose:** View-only resource for dashboards and analytics
**Features:** Charts, counts, summaries of applications, trainees, etc.

### Exporters

Located in `app/Filament/Exporters/`, these handle exporting Filament lists to various formats:
- CSV exporter
- Excel exporter (via PHPOffice/PhpSpreadsheet)
- PDF exporter (via DOMPDF or similar)

---

## HTTP Controllers

Controllers handle API requests and form submissions from the Livewire front-end.

### ApplicationFormController (`app/Http/Controllers/ApplicationFormController.php`)

**Purpose:** Provide JSON endpoints for the Trainee application form.

**Key Methods:**
- `showWelcome()` — Display welcome page
- `showForm()` — Display the main trainee form
- `address()` — Return list of governorates/addresses (JSON)
- `institution()` — Return list of institutions (JSON)
- `major()` — Return list of majors (JSON)
- `majorCollege()` — Return major→college mapping (JSON)
- `administrative()` — Return administrative divisions (JSON)
- `department()` — Return departments (JSON)
- `section()` — Return sections (JSON)
- `trainingType()` — Return available training types (JSON)
- `checkNationalId()` — Validate national ID uniqueness (JSON)
- `checkExistingApplication()` — Check if trainee already applied (JSON)

**Middleware:** CSRF-protected, rate-limited (60 requests/minute)

### DownloadAbsorptionPaperController (`app/Http/Controllers/DownloadAbsorptionPaperController.php`)

**Purpose:** Handle downloads of training-related documents (absorption papers, certificates).
**Key Methods:**
- `__invoke()` — Download a specific document for an application
**Authorization:** Checked via policy (`can:downloadAbsorptionPaper,application`)

### Base Controller (`app/Http/Controllers/Controller.php`)

Parent class for all controllers, provides common functionality and middleware.

---

## Filament Pages & Widgets

### Pages (in `app/Filament/Pages/`)
Custom pages for specific admin functions, such as:
- Dashboard (system overview)
- Settings (configure system parameters)
- Custom reports

### Widgets (in `app/Filament/Widgets/`)
Dashboard widgets providing real-time data visualization:
- Application counts by status
- Trainee enrollment charts
- Department capacity usage
- Quick action cards

---

## Route Structure

All routes are defined in `routes/web.php` and are served via Livewire or controller methods.

**Public Routes:**
- `GET /` → Redirect to `/home` or `/welcome` based on auth
- `GET /welcome` → WelcomeForm Livewire component
- `GET /welcome/form` → TraineeForm Livewire component
- `GET /welcome/form/api/*` → API endpoints for form population

**Protected Routes (Admin - via Filament):**
- `/admin/*` → Filament admin resources and dashboards (authentication required)

**Downloads:**
- `GET /applications/{application}/absorption-paper` → Download document (authorized)

---

## Component Data Flow

```
Public User
  ↓
/ (root)
  ↓
/welcome (WelcomeForm Livewire)
  ↓
/welcome/form (TraineeForm Livewire)
  ├─ Calls /welcome/form/api/* endpoints (ApplicationFormController)
  │  ├─ Fetches institutions, majors, departments, etc.
  │  └─ Validates national ID, existing applications
  ├─ Form submission
  └─ Creates Application & Trainee records
       ↓
Admin User (logged in)
  ↓
/admin (Filament dashboard)
  ├─ Applications resource
  ├─ Trainees resource
  ├─ Organization hierarchy resources
  ├─ Statistics & reports
  └─ Export actions (CSV, Excel, PDF)
```

---

## Component Lifecycle & State Management

- **Livewire** manages client-side state (form data, validation errors)
- **Database** (Eloquent models) persists state server-side
- **Filament** reads from database and provides management interface
- **Controllers** serve as a bridge between form and database, handling validation and business logic

---

## Performance Considerations

- Form selects populate via AJAX calls, not initial page load (reduces payload)
- Livewire components use debouncing for real-time validation
- Filament resources implement pagination for large lists
- Export actions may queue jobs for large datasets
