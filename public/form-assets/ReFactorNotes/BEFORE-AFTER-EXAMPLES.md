# Before & After Code Examples

This document shows side-by-side comparisons of the refactoring.

---

## Example 1: Theme Management

### ❌ BEFORE (Procedural)

```javascript
// Global variable
const themeToggle = document.getElementById("themeToggle");

// Inline event listener
if (themeToggle) {
    themeToggle.addEventListener("click", () => {
        const currentTheme =
            document.documentElement.getAttribute("data-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";

        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);

        // Micro-animation for the icon
        const icon = themeToggle.querySelector(".mode-icon");
        if (icon) {
            icon.style.transform = "scale(0.5) rotate(180deg)";
            setTimeout(() => {
                icon.style.transform = "scale(1) rotate(360deg)";
            }, 150);
        }
    });
}
```

**Problems:**

-   Global variable pollution
-   Logic mixed with initialization
-   Hard to test
-   Hard to reuse

### ✅ AFTER (Class-Based)

```javascript
/**
 * ThemeManager - Handles theme switching logic
 */
class ThemeManager {
    constructor() {
        this.themeToggle = document.getElementById("themeToggle");
        this.init();
    }

    init() {
        if (this.themeToggle) {
            this.themeToggle.addEventListener("click", () =>
                this.toggleTheme()
            );
        }
    }

    toggleTheme() {
        const currentTheme =
            document.documentElement.getAttribute("data-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";

        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);

        this.animateIcon();
    }

    animateIcon() {
        const icon = this.themeToggle?.querySelector(".mode-icon");
        if (icon) {
            icon.style.transform = "scale(0.5) rotate(180deg)";
            setTimeout(() => {
                icon.style.transform = "scale(1) rotate(360deg)";
            }, 150);
        }
    }
}

// Usage
const themeManager = new ThemeManager();
```

**Benefits:**

-   Encapsulated state and behavior
-   Clear responsibilities
-   Easy to test
-   Can be reused

---

## Example 2: Notification Management

### ❌ BEFORE (Scattered Functions)

```javascript
const message = document.getElementById("formMessage");

const setMessage = (text, type = "note") => {
    message.textContent = text;
    message.className =
        type === "error" ? "error" : type === "success" ? "success" : "note";
};

const showToast = (text, type = "success") => {
    const toast = document.getElementById("toast");
    const toastMessage = document.getElementById("toastMessage");
    if (!toast || !toastMessage) return;

    toastMessage.textContent = text;
    toast.className = "toast";
    if (type === "error") {
        toast.classList.add("error");
    }

    setTimeout(() => {
        toast.classList.add("show");
    }, 10);

    setTimeout(() => {
        toast.classList.remove("show");
    }, 3000);
};

// Usage
setMessage("Error", "error");
showToast("Success!", "success");
```

**Problems:**

-   Functions rely on global `message` variable
-   No cleanup of timeouts
-   Can't clear message easily
-   Hard to extend

### ✅ AFTER (Class-Based)

```javascript
/**
 * NotificationManager - Handles messages and toasts
 */
class NotificationManager {
    constructor() {
        this.messageElement = document.getElementById("formMessage");
        this.toastElement = document.getElementById("toast");
        this.toastMessageElement = document.getElementById("toastMessage");
        this.toastTimeoutId = null;
    }

    setMessage(text = "", type = "note") {
        if (!this.messageElement) return;
        this.messageElement.textContent = text;
        this.messageElement.className =
            type === "error"
                ? "error"
                : type === "success"
                ? "success"
                : "note";
    }

    showToast(text, type = "success") {
        if (!this.toastElement || !this.toastMessageElement) return;

        // Clear any existing timeout
        if (this.toastTimeoutId) {
            clearTimeout(this.toastTimeoutId);
        }

        this.toastMessageElement.textContent = text;
        this.toastElement.className = "toast";

        if (type === "error") {
            this.toastElement.classList.add("error");
        }

        // Show the toast
        setTimeout(() => {
            this.toastElement.classList.add("show");
        }, 10);

        // Hide after duration
        this.toastTimeoutId = setTimeout(() => {
            this.toastElement.classList.remove("show");
        }, CONFIG.TOAST_DURATION);
    }

    clearMessage() {
        this.setMessage("", "note");
    }
}

// Usage
const notificationManager = new NotificationManager();
notificationManager.setMessage("Error", "error");
notificationManager.showToast("Success!", "success");
notificationManager.clearMessage();
```

**Benefits:**

-   Encapsulated with all related data
-   Proper timeout management
-   Clear and documented
-   Easy to extend (e.g., add new notification types)

---

## Example 3: Input Validation

### ❌ BEFORE (Scattered, Hardcoded)

```javascript
// Hardcoded constants scattered throughout
const MAX_FILE_SIZE = 2 * 1024 * 1024;
const ALLOWED_TYPES = ["image/jpeg", "image/png", "application/pdf"];
const NATIONAL_ID_LENGTH = 9;

// Validation logic scattered in event handlers
if (letterFileInput) {
    letterFileInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (!file) {
            filePreview.style.display = "none";
            return;
        }

        if (file.size > MAX_FILE_SIZE) {
            showToast("حجم الملف كبير جداً. الحد الأقصى: 2MB", "error");
            letterFileInput.value = "";
            filePreview.style.display = "none";
            return;
        }

        if (!ALLOWED_TYPES.includes(file.type)) {
            showToast(
                "نوع الملف غير مدعوم. الأنواع المدعومة: JPG, PNG, PDF",
                "error"
            );
            letterFileInput.value = "";
            filePreview.style.display = "none";
            return;
        }
        // ... more logic
    });
}

if (nationalIdInput) {
    nationalIdInput.addEventListener("blur", async (e) => {
        const nationalId = e.target.value;
        if (nationalId.length === 9) {
            // Hardcoded!
            // ... validation
        }
    });
}

function handleNameInput(event) {
    const input = event.target;
    input.value = input.value.replace(/[^A-Za-z\u0600-\u06FF\s]/g, "");
}
```

**Problems:**

-   Constants hardcoded throughout
-   Validation logic scattered across handlers
-   Duplicate validation rules
-   Hard to maintain
-   Hard to change validation rules in one place

### ✅ AFTER (Centralized)

```javascript
// Configuration in one place
const CONFIG = {
    MAX_FILE_SIZE: 2 * 1024 * 1024,
    ALLOWED_FILE_TYPES: ["image/jpeg", "image/png", "application/pdf"],
    NATIONAL_ID_LENGTH: 9,
};

/**
 * InputValidator - Handles input validation
 */
class InputValidator {
    static isValidNationalId(nationalId) {
        return nationalId && nationalId.length === CONFIG.NATIONAL_ID_LENGTH;
    }

    static sanitizeNameInput(input) {
        return input.replace(/[^A-Za-z\u0600-\u06FF\s]/g, "");
    }

    static validateFileSize(file) {
        return file.size <= CONFIG.MAX_FILE_SIZE;
    }

    static validateFileType(file) {
        return CONFIG.ALLOWED_FILE_TYPES.includes(file.type);
    }
}

// Usage in file preview handler
if (letterFileInput) {
    letterFileInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (!file) {
            filePreview.style.display = "none";
            return;
        }

        if (!InputValidator.validateFileSize(file)) {
            notificationManager.showToast(
                "حجم الملف كبير جداً. الحد الأقصى: 2MB",
                "error"
            );
            letterFileInput.value = "";
            filePreview.style.display = "none";
            return;
        }

        if (!InputValidator.validateFileType(file)) {
            notificationManager.showToast(
                "نوع الملف غير مدعوم. الأنواع المدعومة: JPG, PNG, PDF",
                "error"
            );
            letterFileInput.value = "";
            filePreview.style.display = "none";
            return;
        }
    });
}

// Usage in national ID validation
if (nationalIdInput) {
    nationalIdInput.addEventListener("blur", async (e) => {
        const nationalId = e.target.value;
        if (InputValidator.isValidNationalId(nationalId)) {
            // ... validation
        }
    });
}

// Usage in name filter
function filterInput(event) {
    event.target.value = InputValidator.sanitizeNameInput(event.target.value);
}
```

**Benefits:**

-   Constants in CONFIG object
-   Validation rules reusable across codebase
-   One place to change rules
-   Easy to add new validators
-   Self-documenting method names

---

## Example 4: Event Listener Organization

### ❌ BEFORE (Scattered Throughout File)

```javascript
// Line 100
if (governorateSelect) {
    governorateSelect.addEventListener("change", () => {
        setMessage("");
    });
}

// Line 130
administrativeSelect.addEventListener("change", (e) => {
    const adminId = e.target.value;
    const trainingType = trainingTypeSelect ? trainingTypeSelect.value : "";
    loadOptions(
        departmentSelect,
        endpoints.department(adminId, trainingType),
        [],
        "name"
    );
    populateOptions(sectionSelect, []);
});

// Line 150
institutionSelect.addEventListener("change", (e) => {
    const instId = e.target.value;
    loadOptions(majorSelect, endpoints.major(instId), [], "name");
});

// ... more scattered listeners ...

// Line 400
(async function init() {
    await loadOptions(institutionSelect, endpoints.institution, []);
    await loadOptions(trainingTypeSelect, endpoints.trainingFocus, []);
    // ... initialization
})();
```

**Problems:**

-   Listeners scattered throughout 400+ lines
-   Hard to find where specific listeners are attached
-   Hard to understand dependencies between listeners
-   Initialization code mixed with other code

### ✅ AFTER (Organized in DOMContentLoaded)

```javascript
// Single entry point for all initialization
document.addEventListener("DOMContentLoaded", () => {
    // Initialize managers
    const themeManager = new ThemeManager();
    const notificationManager = new NotificationManager();
    const selectManager = new SelectManager();
    const filePreviewManager = new FilePreviewManager(notificationManager);
    const dobManager = new DateOfBirthManager();
    const formHandler = new FormHandler(selectManager, notificationManager);
    const nationalIdValidator = new NationalIdValidator(notificationManager);
    const inputNameFilter = new InputNameFilter();

    // All event listeners organized here
    const governorateSelect = selectManager.selects.governorate;
    if (governorateSelect) {
        governorateSelect.addEventListener("change", () =>
            notificationManager.clearMessage()
        );
    }

    selectManager.selects.administrative?.addEventListener(
        "change",
        async (e) => {
            const adminId = e.target.value;
            const trainingType =
                selectManager.selects.trainingType?.value ?? "";
            await selectManager.loadOptions(
                selectManager.selects.department,
                ENDPOINTS.department(adminId, trainingType)
            );
            selectManager.populateOptions(selectManager.selects.section, []);
        }
    );

    selectManager.selects.institution?.addEventListener("change", async (e) => {
        await selectManager.loadOptions(
            selectManager.selects.major,
            ENDPOINTS.major(e.target.value)
        );
    });

    // ... more listeners ...

    // Initialize form data
    (async () => {
        await selectManager.loadOptions(
            selectManager.selects.institution,
            ENDPOINTS.institution
        );
        await selectManager.loadOptions(
            selectManager.selects.trainingType,
            ENDPOINTS.trainingType
        );
        selectManager.populateOptions(selectManager.selects.section, []);
        await selectManager.loadAdministrative();

        // Animate page elements
        FormAnimator.animatePageElements();
    })();
});
```

**Benefits:**

-   All initialization in one place
-   Clear dependencies between managers
-   Easy to see what happens on page load
-   Listeners organized logically
-   Optional chaining (?.) for safety

---

## Example 5: Error Handling

### ❌ BEFORE (Basic Error Handling)

```javascript
async function loadOptions(select, url, fallbackData, labelKey = "name") {
    if (!select) return;
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error("Request failed");
        const data = await res.json();
        populateOptions(select, data, labelKey);
    } catch (err) {
        populateOptions(select, fallbackData, labelKey); // Silent fallback
    }
}
```

**Problems:**

-   Error silently caught
-   No logging for debugging
-   No context about what failed
-   Generic error message

### ✅ AFTER (Contextual Error Handling)

```javascript
async loadOptions(selectElement, url, labelKey = "name") {
    if (!selectElement) return;
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error("Request failed");
        const data = await res.json();
        this.populateOptions(selectElement, data, labelKey);

        // Auto-select if only 1 option for training_type
        if (data.length === 1 && selectElement.id === "training_type") {
            selectElement.value = data[0].id;
            selectElement.dispatchEvent(new Event("change"));
        }
    } catch (err) {
        console.error("Failed to load options from API:", url, err);
        this.populateOptions(selectElement, []);
    }
}
```

**Benefits:**

-   Error logged with context (URL, error details)
-   Easier to debug
-   Can see what API failed
-   Better error messages in console

---

## Summary of Improvements

| Aspect                | Before          | After                           |
| --------------------- | --------------- | ------------------------------- |
| **Code Organization** | Scattered       | Organized in classes            |
| **Configuration**     | Hardcoded       | CONFIG & ENDPOINTS objects      |
| **Event Listeners**   | Scattered       | Centralized in DOMContentLoaded |
| **Error Handling**    | Silent failures | Contextual logging              |
| **Reusability**       | Low             | High                            |
| **Testability**       | Low             | High                            |
| **Documentation**     | Minimal         | Comprehensive                   |
| **Maintainability**   | Difficult       | Easy                            |

---

## Migration Path

If you have code using the old functions:

```javascript
// Old way
setMessage("Error", "error");
showToast("Success!");

// New way
const notificationManager = new NotificationManager();
notificationManager.setMessage("Error", "error");
notificationManager.showToast("Success!");
```

All old code is still available as comments for reference!
