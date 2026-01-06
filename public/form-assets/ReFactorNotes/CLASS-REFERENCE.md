# Class Reference Guide

## Quick Lookup for New Developers

### ThemeManager

**Location:** Both `app.js` files
**Purpose:** Handle theme switching between light/dark modes
**Methods:**

-   `init()` - Initialize theme toggle listeners
-   `toggleTheme()` - Switch between light and dark theme
-   `animateIcon()` - Animate the theme toggle button

**Usage:**

```javascript
const themeManager = new ThemeManager();
// Automatically initialized in DOMContentLoaded
```

---

### CsrfTokenManager

**Location:** Trainee app `app.js`
**Purpose:** Retrieve CSRF tokens for form submissions
**Methods:**

-   `static getToken()` - Get CSRF token from meta tag or cookie

**Usage:**

```javascript
const token = CsrfTokenManager.getToken();
// Used in fetch requests
```

---

### NotificationManager

**Location:** Trainee app `app.js`
**Purpose:** Manage all user-facing notifications
**Methods:**

-   `setMessage(text, type)` - Display inline message (note/error/success)
-   `showToast(text, type)` - Show toast notification
-   `clearMessage()` - Clear inline message

**Usage:**

```javascript
const notificationManager = new NotificationManager();
notificationManager.showToast("Success!", "success");
notificationManager.setMessage("Error occurred", "error");
```

---

### InputValidator

**Location:** Trainee app `app.js`
**Purpose:** Validate user inputs
**Methods:**

-   `static isValidNationalId(nationalId)` - Check if national ID is 9 digits
-   `static sanitizeNameInput(input)` - Remove non-letter characters
-   `static validateFileSize(file)` - Check if file ≤ 2MB
-   `static validateFileType(file)` - Check if file is JPG/PNG/PDF

**Usage:**

```javascript
if (InputValidator.validateFileSize(file)) {
    // File is OK
}
```

---

### SelectManager

**Location:** Trainee app `app.js`
**Purpose:** Manage all dropdown/select fields
**Methods:**

-   `populateOptions(selectElement, items, labelKey)` - Fill select with options
-   `async loadOptions(selectElement, url, labelKey)` - Load options from API
-   `async loadAdministrative()` - Load administrative options by training type
-   `toggleUniversityFields(isUniversity)` - Show/hide university-specific fields

**Properties:**

```javascript
selectManager.selects = {
    governorate,
    institution,
    major,
    administrative,
    department,
    section,
    trainingType,
};
selectManager.universityContainer;
```

**Usage:**

```javascript
const selectManager = new SelectManager();
await selectManager.loadOptions(
    selectManager.selects.institution,
    ENDPOINTS.institution
);
```

---

### FilePreviewManager

**Location:** Trainee app `app.js`
**Purpose:** Handle file upload and preview
**Methods:**

-   `init()` - Attach file input listener
-   `handleFileChange(event)` - Process selected file
-   `showPreview(file)` - Display file preview
-   `showImagePreview(file)` - Show image preview
-   `showPdfPreview(file)` - Show PDF preview
-   `hidePreview()` - Hide preview

**Usage:**

```javascript
const filePreviewManager = new FilePreviewManager(notificationManager);
// Automatically initialized
```

---

### DateOfBirthManager

**Location:** Trainee app `app.js`
**Purpose:** Combine day/month/year dropdowns into hidden field
**Methods:**

-   `init()` - Attach listeners to day/month/year selects
-   `updateHiddenField()` - Combine and update hidden DOB field

**Usage:**

```javascript
const dobManager = new DateOfBirthManager();
// Automatically initialized
```

---

### FormHandler

**Location:** Trainee app `app.js`
**Purpose:** Handle form submission and validation
**Methods:**

-   `init()` - Setup form listeners
-   `updateSubmitButton(event)` - Enable/disable submit button based on terms checkbox
-   `async handleSubmit(event)` - Process form submission
-   `async ensureCollegeId()` - Fetch college ID if missing
-   `async handleResponse(response)` - Process API response

**Usage:**

```javascript
const formHandler = new FormHandler(selectManager, notificationManager);
// Automatically initialized
```

---

### NationalIdValidator

**Location:** Trainee app `app.js`
**Purpose:** Validate national ID uniqueness in real-time
**Methods:**

-   `init()` - Attach blur listener to national ID field
-   `async validateOnBlur(event)` - Check if national ID already exists

**Usage:**

```javascript
const nationalIdValidator = new NationalIdValidator(notificationManager);
// Automatically initialized
```

---

### InputNameFilter

**Location:** Trainee app `app.js`
**Purpose:** Filter name input (letters and spaces only)
**Methods:**

-   `init()` - Attach input listener
-   `filterInput(event)` - Remove invalid characters

**Usage:**

```javascript
const inputNameFilter = new InputNameFilter();
// Automatically initialized
```

---

### FormAnimator

**Location:** Both `app.js` files
**Purpose:** Animate page elements on load
**Methods:**

-   `static animatePageElements()` - Animate hero and form card
-   `static animateElement(element, initialTransform, duration)` - Generic element animation

**Usage:**

```javascript
FormAnimator.animatePageElements();
// Or animate custom elements
FormAnimator.animateElement(element, "translateY(20px)", 600);
```

---

### PageAnimator

**Location:** Welcome `app.js`
**Purpose:** Handle welcome page animations
**Methods:**

-   `static animateOnLoad()` - Animate welcome page elements
-   `static animateElement(element, initialTransform, duration)` - Generic animation

**Usage:**

```javascript
PageAnimator.animateOnLoad();
```

---

## Configuration Objects

### CONFIG

```javascript
const CONFIG = {
    TRAINING_TYPE_UNIVERSITY: 1,
    TRAINING_TYPE_PRACTICE: 2,
    MAX_FILE_SIZE: 2 * 1024 * 1024,           // 2MB
    ALLOWED_FILE_TYPES: [...],                 // JPG, PNG, PDF
    NATIONAL_ID_LENGTH: 9,
    TOAST_DURATION: 3000,                      // milliseconds
    ANIMATION_DELAY: 100,                      // milliseconds
};
```

### ENDPOINTS

All API routes:

```javascript
const ENDPOINTS = {
    address: "...",
    institution: "...",
    major: (institutionId) => "...",
    majorCollege: (majorId) => "...",
    trainingType: "...",
    administrative: (trainingType) => "...",
    department: (adminId, trainingType) => "...",
    section: (deptId, adminId, trainingType) => "...",
    submit: "...",
    checkNationalId: (nationalId) => "...",
};
```

---

## Common Tasks

### Display a notification

```javascript
notificationManager.showToast("Success!", "success");
notificationManager.setMessage("Error", "error");
```

### Validate user input

```javascript
if (!InputValidator.isValidNationalId(nationalId)) {
    // Show error
}
```

### Load dropdown options

```javascript
await selectManager.loadOptions(
    selectManager.selects.institution,
    ENDPOINTS.institution
);
```

### Submit form with error handling

```javascript
try {
    const response = await fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": CsrfTokenManager.getToken(),
        },
    });
} catch (error) {
    notificationManager.showToast("Error submitting form", "error");
}
```

### Check file before upload

```javascript
if (!InputValidator.validateFileSize(file)) {
    notificationManager.showToast("File too large", "error");
    return;
}
if (!InputValidator.validateFileType(file)) {
    notificationManager.showToast("Invalid file type", "error");
    return;
}
```

---

## Initialization Order

When page loads:

1. `DOMContentLoaded` event fires
2. All manager classes instantiated
3. Event listeners attached
4. API data loaded (institutions, training types)
5. Page animations start
6. Form ready for user input

---

## Debugging Tips

### Check if manager is initialized

```javascript
console.log(notificationManager); // Should show class instance
```

### Test notifications

```javascript
notificationManager.showToast("Test message", "success");
notificationManager.setMessage("Test inline", "error");
```

### Check select population

```javascript
console.log(selectManager.selects.institution); // Check if populated
```

### Monitor API calls

```javascript
// Open browser DevTools → Network tab
// See all fetch requests to /WelcomeForm/Form/api/*
```

---

## Performance Considerations

✅ **Debouncing:** Real-time validations (national ID) have blur listener (not on every keystroke)
✅ **Lazy Loading:** API data only loaded when needed
✅ **DOM Caching:** Managers store DOM references, don't re-query
✅ **Async/Await:** Non-blocking API calls
✅ **Event Delegation:** Listeners attached to specific elements, not document

---

## Browser Compatibility

The refactored code uses:

-   ✅ ES6 Classes
-   ✅ Arrow Functions
-   ✅ Template Literals
-   ✅ Async/Await
-   ✅ Optional Chaining (?.)
-   ✅ Nullish Coalescing (??)

**Requires:** Chrome 80+, Firefox 75+, Safari 14+, Edge 80+
(Not IE11 compatible)

---

## Contributing

When adding new features:

1. Create a new manager class if it's a distinct concern
2. Follow single responsibility principle
3. Add JSDoc comments
4. Update this reference guide
5. Keep old code commented for reference
6. Test all edge cases
