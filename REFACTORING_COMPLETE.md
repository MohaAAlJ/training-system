# Form Refactoring - Complete ✅

## Overview
The Trainee Form has been successfully refactored following Laravel/Livewire best practices with all code organized in the `app/Livewire/Trainee/` folder structure.

## Changes Made

### 1. **New Component Structure** (app/Livewire/Trainee/Components/)
All form sections are now separate, reusable Livewire components:

- **FieldsetTrainingType.blade.php** - Training type selection & national ID input
  - Displays training type dropdown and 9-digit national ID field
  - Shows validation error messages inline
  - Readonly support for national ID when validation status requires

- **FieldsetPersonalDetails.blade.php** - Personal information (conditional display)
  - Full name, date of birth, phone number, governorate, street address
  - Phone format: 9705XXXXXXXX or 9725XXXXXXXX (12 digits)
  - DOB age range: 20-60 years old
  - Conditionally shown after ID validation via @if ($showPersonalDetails)

- **FieldsetTrainingDetails.blade.php** - Training preferences (conditional display)
  - Institution, major, training hours, administrative location, department, section
  - Alpine.js cascading selects for intelligent filtering
  - University-only fields conditional on training type = 1
  - Section shows "(ممتلئ)" for full sections

- **FormTerms.blade.php** - Terms & conditions checkbox (conditional display)
  - Attestation about data accuracy
  - Warning about 7-day attendance requirement
  - Tied to termsApproval Livewire property

- **FormFooter.blade.php** - Submit button section (conditional display)
  - Shows spinner + "جاري الفحص..." during validation
  - Disabled until terms checkbox is checked and validation complete

### 2. **CSS Organization** (resources/css/livewire/)
- **trainee-form.css** - All form styling in single, organized file
  - Error states and animations
  - Dark theme support via [data-theme="dark"] selectors
  - Toast notification styling
  - Fieldset and input styling
  - Button hover effects and spinner animation

### 3. **Main Template Refactoring** (resources/views/livewire/trainee/trainee-form.blade.php)
- Replaced inline fieldset HTML with Livewire component includes:
  ```blade
  <livewire:trainee.components.fieldset-training-type />
  <livewire:trainee.components.fieldset-personal-details />
  <livewire:trainee.components.fieldset-training-details />
  <livewire:trainee.components.form-terms />
  <livewire:trainee.components.form-footer />
  ```
- Moved all CSS to @vite import: `resources/css/livewire/trainee-form.css`
- Updated @script section to use Livewire event dispatch for theme toggle
- Kept essential DOM manipulation (input filtering, toast notifications)

### 4. **Theme Toggle Integration** 
- **TraineeForm.php** - Added `toggleTheme()` method that dispatches Livewire event
- **trainee-form.blade.php** - Updated theme toggle button to use `wire:click="toggleTheme"`
- **@script section** - Listens for 'toggle-theme' event and applies theme via localStorage + DOM attribute

### 5. **JavaScript Removal**
- ❌ Deleted: `resources/js/components/trainee-form.js` (vanilla JS file)
- ✅ Replaced with: Livewire-only approach using `wire:click` and `@script` directives
- Input filtering (digits-only for national ID & phone) remains in @script for DOM manipulation
- Toast notifications use Livewire event dispatch: `Livewire.on('show-toast')`

## File Structure After Refactoring

```
app/Livewire/Trainee/
├── TraineeForm.php (Main component, now with toggleTheme() method)
└── Components/
    ├── FieldsetTrainingType.blade.php
    ├── FieldsetPersonalDetails.blade.php
    ├── FieldsetTrainingDetails.blade.php
    ├── FormTerms.blade.php
    └── FormFooter.blade.php

resources/
├── css/livewire/
│   └── trainee-form.css (All form styling)
└── views/livewire/trainee/
    └── trainee-form.blade.php (Clean template using component includes)
```

## Key Features Preserved

✅ **Form Validation**
- Phone regex: `/^97(0|2)5\d{8}$/` (9705/9725 + 8 digits)
- DOB age range: minimum 20 years, maximum 60 years
- Validation messages in Arabic (resources/lang/ar/validation.php)

✅ **Conditional Visibility**
- Fieldset 2 (Personal Details) only shows after training type + national ID validation
- Fieldset 3 (Training Details) only shows when personal details visible
- Terms checkbox only shows when personal details visible
- Submit button only shows and enabled when all conditions met

✅ **Alpine.js Cascading Selects**
- Major filtered by Institution ID
- Department filtered by Administrative Location ID
- Section filtered by both Administrative Location and Department IDs
- All client-side filtering remains unchanged

✅ **Theme Toggle**
- Persists to localStorage
- Applies [data-theme="dark"] for CSS dark mode
- Triggered via wire:click="toggleTheme"

✅ **Toast Notifications**
- Dispatched via `$this->dispatch('show-toast', message: $msg, type: 'error')` from PHP
- Displayed for validation errors (3-4 seconds auto-hide)

## Testing Checklist

Before deploying, verify:
- [ ] Form loads without JavaScript errors
- [ ] Training type dropdown works
- [ ] National ID input accepts only digits, max 9 chars
- [ ] Personal details show after valid ID entry
- [ ] Phone input accepts only digits (9705/9725 format)
- [ ] DOB validation enforces 20-60 year age range
- [ ] Cascading selects filter correctly with Alpine.js
- [ ] Terms checkbox enables/disables submit button
- [ ] Submit button shows spinner during validation
- [ ] Form submits to ApplicationFormController::store() on POST /WelcomeForm/Form
- [ ] Validation error messages display as toast notifications
- [ ] Theme toggle persists across page refreshes
- [ ] Dark theme applies correct styles to all components

## Migration Notes

If reverting or debugging:
- **Old CSS files**: `resources/css/components/trainee-form.css` (now unused)
- **Old JS file**: `resources/js/components/trainee-form.js` (now unused)
- **Old component folder**: `resources/views/components/form/` (now unused)
- All functionality is now in the Livewire structure for centralized management

## Best Practices Applied

✅ Blade component naming: PascalCase files → kebab-case in templates
✅ Livewire component organization: All related files in `app/Livewire/Trainee/` directory
✅ CSS organization: Single theme-aware stylesheet per feature
✅ JavaScript-free: All interactive logic via Livewire and Alpine.js
✅ Separation of concerns: HTML → Components, CSS → CSS file, Logic → Livewire PHP
✅ DRY principle: No duplicate form markup, reusable components
