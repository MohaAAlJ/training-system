// ========================================
// NEW REFACTORED CODE - Using Classes & Better Structure
// ========================================

// ==== CONFIGURATION & CONSTANTS ====
const CONFIG = {
    TRAINING_TYPE_UNIVERSITY: 1,
    TRAINING_TYPE_PRACTICE: 2,
    MAX_FILE_SIZE: 2 * 1024 * 1024, // 2MB
    ALLOWED_FILE_TYPES: ["image/jpeg", "image/png", "application/pdf"],
    NATIONAL_ID_LENGTH: 9,
    TOAST_DURATION: 3000,
    ANIMATION_DELAY: 100,
};

// API Endpoints configuration
const ENDPOINTS = {
    address: "/WelcomeForm/Form/api/address",
    institution: "/WelcomeForm/Form/api/institution",
    major: (institutionId) =>
        `/WelcomeForm/Form/api/major?institution_id=${institutionId ?? ""}`,
    majorCollege: (majorId) =>
        `/WelcomeForm/Form/api/major-college?major_id=${majorId ?? ""}`,
    trainingType: "/WelcomeForm/Form/api/training-type",
    administrative: (trainingType) =>
        `/WelcomeForm/Form/api/administrative?training_type=${trainingType ?? ""
        }`,
    department: (adminId, trainingType) =>
        `/WelcomeForm/Form/api/department?administrative_id=${adminId ?? ""
        }&training_type=${trainingType ?? ""}`,
    section: (deptId, adminId, trainingType) =>
        `/WelcomeForm/Form/api/section?department_id=${deptId ?? ""
        }&administrative_id=${adminId ?? ""}&training_type=${trainingType ?? ""
        }`,
    submit: "/WelcomeForm/Form",
    checkNationalId: (nationalId) =>
        `/WelcomeForm/Form/api/check-national-id?national_id=${nationalId ?? ""
        }`,
    checkExistingApplication: (nationalId, trainingType) =>
        `/WelcomeForm/Form/api/check-existing-application?national_id=${nationalId ?? ""
        }&training_type=${trainingType ?? ""}`,
};

// ==== UTILITY CLASSES ====

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

/**
 * CsrfTokenManager - Handles CSRF token retrieval
 */
class CsrfTokenManager {
    static getToken() {
        try {
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                const token = metaToken.getAttribute("content");
                if (token) return token;
            }
        } catch (e) {
            console.warn("Failed to retrieve CSRF token from meta tag", e);
        }

        try {
            const match = document.cookie
                .split(";")
                .map((c) => c.trim())
                .find((c) => c.startsWith("XSRF-TOKEN="));
            if (match) {
                return decodeURIComponent(match.split("=")[1]);
            }
        } catch (e) {
            console.warn("Failed to retrieve CSRF token from cookie", e);
        }

        console.error(
            "WARNING: CSRF token not found. Form submission may fail."
        );
        return "";
    }
}

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

/**
 * SelectManager - Handles select/dropdown population and updates
 */
class SelectManager {
    constructor() {
        this.selects = {
            governorate: document.getElementById("governorate_id"),
            institution: document.getElementById("institution_id"),
            major: document.getElementById("major_id"),
            administrative: document.getElementById("administrative_id"),
            department: document.getElementById("department_id"),
            section: document.getElementById("section_id"),
            trainingType: document.getElementById("training_type"),
        };
        this.institutionField = document.getElementById("institution_field");
        this.majorField = document.getElementById("major_field");
    }

    /**
     * Populate a select with options
     */
    populateOptions(selectElement, items, labelKey = "name") {
        if (!selectElement) return;

        selectElement.innerHTML =
            '<option value="" disabled selected>اختر</option>';
        items.forEach((item) => {
            const opt = document.createElement("option");
            opt.value = item.id;
            opt.textContent = item[labelKey] ?? "";
            if (item.is_full) {
                opt.disabled = true;
                opt.style.color = "#999";
                opt.style.fontStyle = "italic";
            }
            selectElement.appendChild(opt);
        });
    }

    /**
     * Load options from API endpoint
     */
    async loadOptions(selectElement, url, labelKey = "name") {
        if (!selectElement) return;
        try {
            const csrfToken = CsrfTokenManager.getToken();
            console.log("[SelectManager] Loading from:", url, "with token:", csrfToken.substring(0, 10) + "...");

            const res = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!res.ok) {
                console.error("[SelectManager] Failed with status:", res.status);
                throw new Error("Request failed");
            }

            const data = await res.json();
            console.log("[SelectManager] Received:", data.length, "items");
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

    /**
     * Load administrative based on training type
     */
    async loadAdministrative() {
        const trainingType = this.selects.trainingType?.value ?? "";
        await this.loadOptions(
            this.selects.administrative,
            ENDPOINTS.administrative(trainingType)
        );
        this.populateOptions(this.selects.department, []);
        this.populateOptions(this.selects.section, []);
    }

    /**
     * Show/hide university fields based on training type
     */
    toggleUniversityFields(isUniversity) {
        // Show/hide individual university field labels
        if (this.institutionField) {
            this.institutionField.style.display = isUniversity
                ? "flex"
                : "none";
        }
        if (this.majorField) {
            this.majorField.style.display = isUniversity ? "flex" : "none";
        }

        if (this.selects.institution) {
            this.selects.institution.required = isUniversity;
        }
        if (this.selects.major) {
            this.selects.major.required = isUniversity;
        }

        if (!isUniversity) {
            if (this.selects.institution) this.selects.institution.value = "";
            if (this.selects.major) this.selects.major.value = "";
            const collegeInput = document.getElementById("college_id");
            if (collegeInput) collegeInput.value = "";
        }
    }
}

/**
 * FilePreviewManager - Handles file preview functionality
 */
class FilePreviewManager {
    constructor(notificationManager) {
        this.notificationManager = notificationManager;
        this.fileInput = document.getElementById("letter_file");
        this.filePreview = document.getElementById("file_preview");
        this.previewImage = document.getElementById("preview_image");
        this.previewPdf = document.getElementById("preview_pdf");
        this.previewPdfName = document.getElementById("preview_pdf_name");

        this.init();
    }

    init() {
        if (this.fileInput) {
            this.fileInput.addEventListener("change", (e) =>
                this.handleFileChange(e)
            );
        }
    }

    handleFileChange(event) {
        const file = event.target.files[0];
        if (!file) {
            this.hidePreview();
            return;
        }

        // Validate file size
        if (!InputValidator.validateFileSize(file)) {
            this.notificationManager.showToast(
                "حجم الملف كبير جداً. الحد الأقصى: 2MB",
                "error"
            );
            this.fileInput.value = "";
            this.hidePreview();
            return;
        }

        // Validate file type
        if (!InputValidator.validateFileType(file)) {
            this.notificationManager.showToast(
                "نوع الملف غير مدعوم. الأنواع المدعومة: JPG, PNG, PDF",
                "error"
            );
            this.fileInput.value = "";
            this.hidePreview();
            return;
        }

        this.showPreview(file);
    }

    showPreview(file) {
        if (this.filePreview) {
            this.filePreview.style.display = "block";
        }

        if (file.type.startsWith("image/")) {
            this.showImagePreview(file);
        } else if (file.type === "application/pdf") {
            this.showPdfPreview(file);
        } else {
            this.hidePreview();
        }
    }

    showImagePreview(file) {
        if (this.previewImage) {
            this.previewImage.style.display = "block";
        }
        if (this.previewPdf) {
            this.previewPdf.style.display = "none";
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            if (this.previewImage) {
                this.previewImage.src = event.target.result;
            }
        };
        reader.readAsDataURL(file);
    }

    showPdfPreview(file) {
        if (this.previewImage) {
            this.previewImage.style.display = "none";
        }
        if (this.previewPdf) {
            this.previewPdf.style.display = "flex";
            this.previewPdf.style.alignItems = "center";
            this.previewPdf.style.gap = "8px";
        }
        if (this.previewPdfName) {
            this.previewPdfName.textContent = file.name;
        }
    }

    hidePreview() {
        if (this.filePreview) {
            this.filePreview.style.display = "none";
        }
    }
}

/**
 * ApplicationValidator - Check for existing applications on initial form load
 * Uses async/await for clean, modern JavaScript
 */
/**
 * ApplicationValidator - Check if trainee has existing application for same training type
 */
class ApplicationValidator {
    constructor(formHandler) {
        this.formHandler = formHandler;
        this.nationalIdInput = document.getElementById("national_id");
        this.trainingTypeSelect = document.getElementById("training_type");
        this.errorContainer = document.getElementById("applicationErrorContainer");
        this.errorMessage = document.getElementById("applicationErrorMessage");
        this.formContent = document.getElementById("formContent");
        this.isValidating = false;

        // Form fields for pre-filling
        this.fields = {
            fullName: document.getElementById("full_name"),
            dob: document.getElementById("dob"),
            phoneNumber: document.getElementById("phone_number"),
            governorate: document.getElementById("governorate_id"),
            street: document.getElementById("street"),
            institution: document.getElementById("institution_id"),
            major: document.getElementById("major_id"),
            trainingHours: document.getElementById("training_hours")
        };

        this.init();
    }

    init() {
        if (!this.nationalIdInput || !this.trainingTypeSelect) {
            console.warn("ApplicationValidator: Missing form elements");
            return;
        }

        this.nationalIdInput.addEventListener("input", () => this.validate());
        this.trainingTypeSelect.addEventListener("change", () => this.validate());
    }

    /**
     * Main validation - check national_id + training_type for existing application
     */
    async validate() {
        const nationalId = this.nationalIdInput.value?.trim() || "";
        const trainingType = this.trainingTypeSelect.value || "";

        console.log("[ApplicationValidator] Checking:", { nationalId, trainingType });

        // Invalid state - hide everything
        if (!this.isValidInput(nationalId, trainingType)) {
            console.log("[ApplicationValidator] Invalid input - hiding form");
            this.hideError();
            this.hideForm();
            return;
        }

        // Skip if already validating
        if (this.isValidating) return;

        this.isValidating = true;

        try {
            const data = await this.checkApplication(nationalId, trainingType);
            console.log("[ApplicationValidator] API Response:", data);

            // Application found with same training type - block user
            if (data.hasApplication === true) {
                console.log("[ApplicationValidator] Found blocking application - showing error");
                this.showBlockingError(data.message);
                this.hideForm();
            } else {
                // No blocking application - allow continuation
                console.log("[ApplicationValidator] No blocking app - showing form");
                this.hideError();

                // Prefill if trainee data exists
                if (data.trainee) {
                    this.prefillForm(data.trainee);
                } else {
                    this.resetFormRestrictions();
                }

                this.showForm();
            }
        } catch (error) {
            console.error("[ApplicationValidator] Error:", error);
            // Fail gracefully
            this.hideError();
            this.showForm();
        } finally {
            this.isValidating = false;
        }
    }

    /**
     * Validate: 9 digit national_id + training_type selected
     */
    isValidInput(nationalId, trainingType) {
        const isValid = /^\d{9}$/.test(nationalId) && trainingType !== "";
        if (!isValid) {
            console.log("[ApplicationValidator] Invalid input - nationalId length:", nationalId.length, "trainingType:", trainingType);
        }
        return isValid;
    }

    /**
     * Fetch from API
     */
    async checkApplication(nationalId, trainingType) {
        const url = ENDPOINTS.checkExistingApplication(nationalId, trainingType);
        console.log("[ApplicationValidator] Fetching:", url);

        const csrfToken = CsrfTokenManager.getToken();
        const response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return await response.json();
    }

    /**
     * Show blocking error: toast + text message
     */
    showBlockingError(message) {
        console.log("[ApplicationValidator] Displaying error message:", message);

        // Toast notification
        if (window.notificationManager) {
            window.notificationManager.showToast(message, "error");
        }

        // Text message
        if (this.errorContainer && this.errorMessage) {
            this.errorMessage.textContent = message;
            this.errorContainer.style.display = "block";
        }
    }

    hideError() {
        if (this.errorContainer) {
            this.errorContainer.style.display = "none";
        }
    }

    showForm() {
        if (this.formContent) {
            this.formContent.style.display = "block";
        }
    }

    hideForm() {
        if (this.formContent) {
            this.formContent.style.display = "none";
        }
    }

    /**
     * Prefill form with trainee data and set restrictions
     */
    async prefillForm(trainee) {
        console.log("[ApplicationValidator] Prefilling form with:", trainee);

        // 1. Set restricted fields to readonly
        if (this.fields.fullName) {
            this.fields.fullName.value = trainee.full_name || "";
            this.fields.fullName.readOnly = true;
            this.fields.fullName.classList.add("readonly-field");
        }

        if (this.fields.dob) {
            // Handle date format if needed (trainee.dob is usually Y-m-d)
            const dob = trainee.dob ? trainee.dob.split('T')[0] : "";
            this.fields.dob.value = dob;
            // For Flatpickr, we might need to set it via the instance, 
            // but setting value and making it readonly should work for simple cases
            this.fields.dob.readOnly = true;
            this.fields.dob.classList.add("readonly-field");

            // Disable the calendar button
            const calendarBtn = document.getElementById("dobCalendarBtn");
            if (calendarBtn) calendarBtn.style.display = "none";
        }

        if (this.nationalIdInput) {
            this.nationalIdInput.readOnly = true;
            this.nationalIdInput.classList.add("readonly-field");
        }

        // 2. Prefill editable fields
        if (this.fields.phoneNumber) this.fields.phoneNumber.value = trainee.phone_number || "";
        if (this.fields.governorate) this.fields.governorate.value = trainee.governorate_id || "";
        if (this.fields.street) this.fields.street.value = trainee.street || "";
        if (this.fields.trainingHours) this.fields.trainingHours.value = trainee.training_hours || "";

        // 3. Handle cascading selects (Institution -> Major)
        if (this.fields.institution && trainee.institution_id) {
            this.fields.institution.value = trainee.institution_id;
            // Trigger change to load majors
            this.fields.institution.dispatchEvent(new Event("change"));

            // Wait a bit for majors to load then set major
            setTimeout(() => {
                if (this.fields.major && trainee.major_id) {
                    this.fields.major.value = trainee.major_id;
                    this.fields.major.dispatchEvent(new Event("change"));
                }
            }, 500);
        }
    }

    /**
     * Reset restrictions if it's a new trainee
     */
    resetFormRestrictions() {
        if (this.fields.fullName) {
            this.fields.fullName.readOnly = false;
            this.fields.fullName.classList.remove("readonly-field");
        }
        if (this.fields.dob) {
            this.fields.dob.readOnly = false;
            this.fields.dob.classList.remove("readonly-field");
            const calendarBtn = document.getElementById("dobCalendarBtn");
            if (calendarBtn) calendarBtn.style.display = "flex";
        }
        if (this.nationalIdInput) {
            this.nationalIdInput.readOnly = false;
            this.nationalIdInput.classList.remove("readonly-field");
        }
    }
}

/**
 * ArabicDatePicker - Initialize Flatpickr with Arabic localization
 * Displays date picker with Arabic month names
 */
class ArabicDatePicker {
    constructor() {
        this.dobInput = document.getElementById("dob");
        this.calendarBtn = document.getElementById("dobCalendarBtn");
        this.init();
    }

    init() {
        if (!this.dobInput) return;

        const picker = flatpickr(this.dobInput, {
            locale: "ar",
            dateFormat: "Y-m-d",
            mode: "single",
            maxDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)), // Min age: 18
            minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 60)), // Max age: 60
            enableTime: false,
            monthSelectorType: "dropdown",
            altInput: true,
            altFormat: "j F Y",
        });

        // Make calendar button open the date picker
        if (this.calendarBtn) {
            this.calendarBtn.addEventListener("click", (e) => {
                e.preventDefault();
                picker.open();
            });
        }
    }
}

/**
 * DateOfBirthManager - DEPRECATED - Using Filament's datepicker instead
 * This class handled the old select-based DOB (day/month/year) combination.
 * Now using Filament's native datepicker component for better UX.
 */
/*
class DateOfBirthManager {
    constructor() {
        this.dobInput = document.getElementById("dob");
        this.dobDay = document.getElementById("dob_day");
        this.dobMonth = document.getElementById("dob_month");
        this.dobYear = document.getElementById("dob_year");

        this.init();
    }

    init() {
        if (this.dobDay)
            this.dobDay.addEventListener("change", () =>
                this.updateHiddenField()
            );
        if (this.dobMonth)
            this.dobMonth.addEventListener("change", () =>
                this.updateHiddenField()
            );
        if (this.dobYear)
            this.dobYear.addEventListener("change", () =>
                this.updateHiddenField()
            );
    }

    updateHiddenField() {
        if (!this.dobDay || !this.dobMonth || !this.dobYear || !this.dobInput)
            return;

        const day = this.dobDay.value;
        const month = this.dobMonth.value;
        const year = this.dobYear.value;

        if (day && month && year) {
            this.dobInput.value = `${day}/${month}/${year}`;
        } else {
            this.dobInput.value = "";
        }
    }
}
*/

/**
 * FormHandler - Main form submission and validation logic
 */
class FormHandler {
    constructor(selectManager, notificationManager) {
        this.selectManager = selectManager;
        this.notificationManager = notificationManager;
        this.form = document.getElementById("applicationForm");
        this.submitBtn = document.getElementById("submitBtn");
        this.termsCheckbox = document.getElementById("terms_approval");
        this.collegeInput = document.getElementById("college_id");

        this.init();
    }

    init() {
        if (this.form) {
            this.form.addEventListener("submit", (e) => this.handleSubmit(e));
        }

        if (this.termsCheckbox && this.submitBtn) {
            this.termsCheckbox.addEventListener("change", (e) =>
                this.updateSubmitButton(e)
            );
        }
    }

    updateSubmitButton(event) {
        const isChecked = event.target.checked;
        this.submitBtn.disabled = !isChecked;
        this.submitBtn.style.opacity = isChecked ? "1" : "0.5";
        this.submitBtn.style.cursor = isChecked ? "pointer" : "not-allowed";
    }

    async handleSubmit(event) {
        event.preventDefault();
        this.notificationManager.setMessage("جاري الإرسال...", "note");

        try {
            await this.ensureCollegeId();
            const formData = new FormData(this.form);
            const response = await fetch(ENDPOINTS.submit, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": CsrfTokenManager.getToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            await this.handleResponse(response);
        } catch (err) {
            console.error("Form submission error:", err);
            this.notificationManager.showToast(
                "تعذر الإرسال، جرّب لاحقاً.",
                "error"
            );
        }
    }

    async ensureCollegeId() {
        if (this.collegeInput?.value) return;

        const majorId = this.selectManager.selects.major?.value;
        if (!majorId) return;

        try {
            const csrfToken = CsrfTokenManager.getToken();
            const res = await fetch(ENDPOINTS.majorCollege(majorId), {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            if (res.ok) {
                const data = await res.json();
                if (Array.isArray(data) && data.length > 0) {
                    this.collegeInput.value = data[0].id ?? "";
                    if (
                        data[0].institution_id &&
                        this.selectManager.selects.institution
                    ) {
                        this.selectManager.selects.institution.value =
                            data[0].institution_id;
                    }
                }
            }
        } catch (err) {
            console.warn(
                "Could not fetch college for major before submit",
                err
            );
        }
    }

    async handleResponse(response) {
        if (!response.ok) {
            const body = await response.json();
            if (response.status === 422 && body.errors) {
                const errorMessage =
                    body.errors.national_id?.[0] ||
                    Object.values(body.errors)[0]?.[0] ||
                    "حدث خطأ في البيانات المدخلة";
                this.notificationManager.showToast(errorMessage, "error");
                this.notificationManager.setMessage(errorMessage, "error");
                return;
            }
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        this.notificationManager.clearMessage();
        this.notificationManager.showToast(
            "تم إرسال الطلب بنجاح. جاري التحويل...",
            "success"
        );
        this.form.reset();

        setTimeout(() => {
            window.location.href = data.redirect || "/WelcomeForm";
        }, 2000);
    }
}

/**
 * NationalIdValidator - Handles national ID validation
 */
/**
 * NationalIdValidator - DISABLED - Validation now handled by ApplicationValidator
 * This class was checking if national ID already exists in system
 * But we now use ApplicationValidator which checks by BOTH national_id AND training_type
 * Keeping this commented to avoid duplicate validation logic
 */
/*
class NationalIdValidator {
    constructor(notificationManager) {
        this.notificationManager = notificationManager;
        this.input = document.getElementById("national_id");
        this.init();
    }

    init() {
        if (this.input) {
            this.input.addEventListener("blur", (e) => this.validateOnBlur(e));
        }
    }

    async validateOnBlur(event) {
        const nationalId = event.target.value;
        if (!InputValidator.isValidNationalId(nationalId)) return;

        try {
            const res = await fetch(ENDPOINTS.checkNationalId(nationalId));
            if (!res.ok) return;

            const data = await res.json();
            if (data.exists) {
                this.notificationManager.showToast(data.message, "error");
                this.notificationManager.setMessage(data.message, "error");
                event.target.classList.add("invalid");
            } else {
                event.target.classList.remove("invalid");
            }
        } catch (err) {
            console.error("Error checking national ID", err);
        }
    }
}
*/

/**
 * InputNameFilter - Handles name input filtering
 */
class InputNameFilter {
    constructor() {
        this.fullNameInput = document.getElementById("full_name");
        this.init();
    }

    init() {
        if (this.fullNameInput) {
            this.fullNameInput.addEventListener("input", (e) =>
                this.filterInput(e)
            );
        }
    }

    filterInput(event) {
        event.target.value = InputValidator.sanitizeNameInput(
            event.target.value
        );
    }
}

/**
 * FormAnimator - Handles page entrance animations
 */
class FormAnimator {
    static animatePageElements() {
        const heroElement = document.querySelector(".hero");
        const formCard = document.querySelector(".card");

        if (heroElement) {
            this.animateElement(heroElement, "translateY(-20px)", 500);
        }

        if (formCard) {
            setTimeout(() => {
                this.animateElement(formCard, "translateY(20px)", 600);
            }, CONFIG.ANIMATION_DELAY);
        }
    }

    static animateElement(element, initialTransform, duration) {
        element.style.opacity = "0";
        element.style.transform = initialTransform;

        requestAnimationFrame(() => {
            element.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
            element.style.opacity = "1";
            element.style.transform = "translateY(0)";
        });
    }
}

// ========================================
// OLD CODE - KEPT FOR REFERENCE
// ========================================

/*
// --- ORIGINAL THEME TOGGLE LOGIC ---
const themeToggle = document.getElementById("themeToggle");
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

// NOTE: Fallback data object - DEPRECATED and no longer used
// All form data is now loaded from API endpoints
// const fallback = {};

const form = document.getElementById("applicationForm");
const message = document.getElementById("formMessage");
const governorateSelect = document.getElementById("governorate_id");
const institutionSelect = document.getElementById("institution_id");
const majorSelect = document.getElementById("major_id");
const administrativeSelect = document.getElementById("administrative_id");
const departmentSelect = document.getElementById("department_id");
const sectionSelect = document.getElementById("section_id");
const trainingTypeSelect = document.getElementById("training_type");
const dobInput = document.getElementById("dob");
const dobDay = document.getElementById("dob_day");
const dobMonth = document.getElementById("dob_month");
const dobYear = document.getElementById("dob_year");
const fullNameInput = document.getElementById("full_name");
const nationalIdInput = document.getElementById("national_id");

const getCsrfToken = () => {
    try {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            const token = metaToken.getAttribute("content");
            if (token) return token;
        }
    } catch (e) {
        console.warn("Failed to retrieve CSRF token from meta tag", e);
    }

    try {
        const match = document.cookie
            .split(";")
            .map((c) => c.trim())
            .find((c) => c.startsWith("XSRF-TOKEN="));
        if (match) {
            return decodeURIComponent(match.split("=")[1]);
        }
    } catch (e) {
        console.warn("Failed to retrieve CSRF token from cookie", e);
    }

    console.error("WARNING: CSRF token not found. Form submission may fail.");
    return "";
};

function handleNameInput(event) {
    const input = event.target;
    input.value = input.value.replace(/[^A-Za-z\u0600-\u06FF\s]/g, "");
}

function handleDobInput(event) {
    const input = event.target;
    const digits = input.value.replace(/\D/g, "").slice(0, 8);
    const parts = [];
    if (digits.length > 0) parts.push(digits.slice(0, 2));
    if (digits.length > 2) parts.push(digits.slice(2, 4));
    if (digits.length > 4) parts.push(digits.slice(4, 8));
    input.value = parts.join("/");
}

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

const populateOptions = (select, items, labelKey = "name") => {
    if (!select) return;
    select.innerHTML = '<option value="" disabled selected>اختر</option>';
    items.forEach((item) => {
        const opt = document.createElement("option");
        opt.value = item.id;
        opt.textContent = item[labelKey] ?? "";
        if (item.is_full) {
            opt.disabled = true;
            opt.style.color = "#999";
            opt.style.fontStyle = "italic";
        }
        select.appendChild(opt);
    });
};

async function loadOptions(select, url, fallbackData, labelKey = "name") {
    if (!select) return;
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error("Request failed");
        const data = await res.json();
        populateOptions(select, data, labelKey);

        if (data.length === 1 && select.id === "training_type") {
            select.value = data[0].id;
            select.dispatchEvent(new Event("change"));
        }
    } catch (err) {
        populateOptions(select, fallbackData, labelKey);
    }
}

async function loadAdministrative() {
    const trainingType = trainingTypeSelect ? trainingTypeSelect.value : "";
    await loadOptions(
        administrativeSelect,
        endpoints.administrative(trainingType),
        []
    );
    populateOptions(departmentSelect, []);
    populateOptions(sectionSelect, []);
}

// ... [rest of original event listeners] ...
*/

// ========================================
// APPLICATION INITIALIZATION
// ========================================

document.addEventListener("DOMContentLoaded", () => {
    // Initialize managers in order (some depend on others)
    const themeManager = new ThemeManager();
    const notificationManager = new NotificationManager();
    const selectManager = new SelectManager();
    const filePreviewManager = new FilePreviewManager(notificationManager);
    const arabicDatePicker = new ArabicDatePicker(); // Initialize Arabic date picker
    const formHandler = new FormHandler(selectManager, notificationManager);
    const applicationValidator = new ApplicationValidator(formHandler); // Check for existing applications
    // const nationalIdValidator = new NationalIdValidator(notificationManager); // DISABLED - ApplicationValidator now handles all validation
    const inputNameFilter = new InputNameFilter();
    // const dobManager = new DateOfBirthManager(); // DEPRECATED - Using Filament's datepicker instead

    // Setup event listeners for cascading selects
    const governorateSelect = selectManager.selects.governorate;
    if (governorateSelect) {
        governorateSelect.addEventListener("change", () =>
            notificationManager.clearMessage()
        );
    }

    // Administrative change listener
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

    // Institution change listener
    selectManager.selects.institution?.addEventListener("change", async (e) => {
        await selectManager.loadOptions(
            selectManager.selects.major,
            ENDPOINTS.major(e.target.value)
        );
    });

    // Department change listener
    selectManager.selects.department?.addEventListener("change", async (e) => {
        const deptId = e.target.value;
        const adminId = selectManager.selects.administrative?.value ?? "";
        const trainingType = selectManager.selects.trainingType?.value ?? "";
        await selectManager.loadOptions(
            selectManager.selects.section,
            ENDPOINTS.section(deptId, adminId, trainingType)
        );
    });

    // Major change listener
    selectManager.selects.major?.addEventListener("change", async (e) => {
        try {
            const csrfToken = CsrfTokenManager.getToken();
            const res = await fetch(ENDPOINTS.majorCollege(e.target.value), {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            if (!res.ok) return;
            const data = await res.json();

            if (Array.isArray(data) && data.length > 0) {
                const first = data[0];
                if (first.institution_id && selectManager.selects.institution) {
                    selectManager.selects.institution.value =
                        first.institution_id;
                }
                const collegeInput = document.getElementById("college_id");
                if (collegeInput) {
                    collegeInput.value = first.id ?? "";
                }
            } else {
                const collegeInput = document.getElementById("college_id");
                if (collegeInput) collegeInput.value = "";
            }
        } catch (err) {
            console.error("Error loading college for major:", err);
            const collegeInput = document.getElementById("college_id");
            if (collegeInput) collegeInput.value = "";
        }
    });

    // Training type change listener
    selectManager.selects.trainingType?.addEventListener(
        "change",
        async (e) => {
            const isUniversity =
                parseInt(e.target.value) === CONFIG.TRAINING_TYPE_UNIVERSITY;
            selectManager.toggleUniversityFields(isUniversity);
            await selectManager.loadAdministrative();
        }
    );

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
