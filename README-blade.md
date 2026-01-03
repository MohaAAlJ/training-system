**Overview**

This README documents the public-facing Blade layer of the application: templates, controllers/Livewire that render them, routes, assets, and the user data flow for trainee applications and related pages.

**Where to look**

- Views: `resources/views`
- Controllers: `app/Http/Controllers`
- Livewire components (if present): `app/Http/Livewire`
- Routes: `routes/web.php`
- Public assets/styles: `public/form-assets/trainee-app`, `resources/css`, `resources/js`

**Entry points & routes**

- Public endpoints are defined in `routes/web.php` and map to controller methods or Livewire components that return Blade views.
- Common actions: show application form, submit application, show confirmations, and list trainee info.

**Templates & structure**

- Master layout (typically in `resources/views/layouts`) includes the HTML skeleton, global CSS/JS, and yield sections.
- Page templates extend the master layout and implement sections for content and scripts.
- Use partials/components for repeated UI (nav, alerts, form fields).

**Controllers / Livewire responsibilities**

- Controllers prepare data (Eloquent models/collections), validate requests, persist models (e.g., `Trainee`, `Application`) and trigger notifications.
- Livewire components (if used) handle reactive UI, validation, and server interactions directly.

**Data flow (typical)**

1. User requests a route → route resolves to controller/Livewire.
2. Controller/Livewire loads models and passes variables to the Blade view.
3. User submits forms → controller validates, saves models, dispatches notifications/events.
4. Controller redirects to confirmation or returns view with validation errors.

**Assets & builds**

- Development builds: Vite configured via `vite.config.js`; source assets live in `resources/js` and `resources/css`.
- Static CSS used by the trainee form: `public/form-assets/trainee-app/styles.css`.
- Production assets are generated into `public/build` (check project build scripts in `package.json`).

**Notifications & side effects**

- After certain actions, controllers may send notifications like `ApplicationCreated`, `InitialApprovalNotification`, `TraineeStartedNotification` located in `app/Notifications`.

**Where to change behavior**

- Modify template markup in `resources/views`.
- Update request handling and validation in the corresponding controller in `app/Http/Controllers` or Livewire class.
- Update styles in `public/form-assets/trainee-app` or source files and rebuild.

**Optional: generate repo-specific mapping**

If you want, I can scan the repository and produce a detailed mapping of each Blade file to its controller/Livewire, the exact variables passed, and the routes that target them.
