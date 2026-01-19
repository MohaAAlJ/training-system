# TraineeForm Component - Directory Structure

## Recommended Organization

```
app/Livewire/Trainee/
├── TraineeForm.php                    # Main Livewire component
├── WelcomeForm.php                    # Welcome/landing page component
├── config/
│   └── TraineeFormConfig.php          # Centralized configuration (BEST PRACTICE)
├── Concerns/
│   ├── ManagesFormState.php           # Form state management trait
│   ├── HandlesValidation.php          # Validation logic trait
│   ├── FetchesFormData.php            # Data fetching trait
│   └── HandlesFileUpload.php          # File upload logic trait
├── Actions/
│   ├── SubmitApplicationAction.php    # Submit form action
│   ├── ValidateApplicationAction.php  # Complex validation logic
│   └── CheckExistingApplicationAction.php
└── Services/
    ├── ApplicationService.php         # Application business logic
    └── FormDataService.php            # Form data retrieval

resources/views/livewire/trainee/
├── trainee-form.blade.php             # Main form template
├── components/
│   ├── fieldset-training-type.blade.php      # Reusable fieldset component
│   ├── fieldset-personal-details.blade.php   # Reusable fieldset component
│   ├── fieldset-training-details.blade.php   # Reusable fieldset component
│   ├── form-terms.blade.php                  # Terms section component
│   └── form-footer.blade.php                 # Submit button component
└── partials/
    ├── status-message.blade.php       # Status message partial
    └── toast-notification.blade.php   # Toast partial

resources/css/
└── components/
    ├── trainee-form.css               # Main form styles
    ├── fieldset-training-type.css     # Scoped fieldset styles
    ├── fieldset-personal-details.css  # Scoped fieldset styles
    ├── fieldset-training-details.css  # Scoped fieldset styles
    ├── form-terms.css                 # Terms section styles
    └── form-footer.css                # Footer styles

routes/
├── web.php                            # Main routes (consolidate Livewire routes here)
└── livewire-forms.php                 # Livewire-specific routes (kept for backwards compatibility)
```

## Best Practices Applied

### 1. **Configuration Separation** ✅
   - Moved all constants and validation rules to `config/TraineeFormConfig.php`
   - Benefits: Centralized, reusable, testable

### 2. **Trait-Based Concerns** ✅
   - Split component logic into focused traits
   - Benefits: Single Responsibility, reusability, testability

### 3. **Service/Action Classes** (TODO)
   - Extract complex business logic into dedicated classes
   - Benefits: Easier to test, reuse across components

### 4. **Component-Based Views** ✅
   - Each fieldset is a separate Blade component
   - Benefits: Reusability, easier maintenance

### 5. **Scoped CSS** ✅
   - CSS files match component structure
   - Benefits: No style conflicts, easier to locate styles

### 6. **Route Organization** ✅
   - Consolidated Livewire routes in `web.php`
   - Backwards compatible aliases for old routes
   - Grouped API routes with middleware

### 7. **Backwards Compatibility** ✅
   - Old routes `/WelcomeForm`, `/WelcomeForm/Form` still work
   - New clean routes `/welcome`, `/trainee-form` also available
   - Old POST route removed (Livewire handles submission)

## Migration Path

If coming from vanilla JS:
1. Old URLs still work via aliases
2. No additional changes needed
3. All validation is now reactive
4. File uploads handled by Livewire

## Next Steps to Implement

Create these files for complete refactoring:
1. `app/Livewire/Trainee/Concerns/HandlesFakeSubmit.php` - Form submission logic
2. `app/Livewire/Trainee/Services/FormDataService.php` - Data fetching
3. `app/Livewire/Trainee/Actions/SubmitApplicationAction.php` - Application submission
