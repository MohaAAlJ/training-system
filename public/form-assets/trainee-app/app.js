// الإعدادات: حالياً تشير لملفات JSON محلية داخل مجلد النموذج
const endpoints = {
    addresses: "/WelcomeForm/Form/api/addresses",
    institutions: "/WelcomeForm/Form/api/institutions",
    majors: (institutionId) =>
        `/WelcomeForm/Form/api/majors?institution_id=${institutionId ?? ""}`,
    majorColleges: (majorId) =>
        `/WelcomeForm/Form/api/major-colleges?major_id=${majorId ?? ""}`,
    trainingFocus: "/WelcomeForm/Form/api/training-types",
    administratives: "/WelcomeForm/Form/api/administratives",
    departments: (adminId) =>
        `/WelcomeForm/Form/api/departments?administrative_id=${adminId ?? ""}`,
    sections: (deptId, adminId) =>
        `/WelcomeForm/Form/api/sections?department_id=${
            deptId ?? ""
        }&administrative_id=${adminId ?? ""}`,
    submit: "/WelcomeForm/Form",
};

// بيانات بديلة مؤقتة (أزلها عند توفر الـ API)
const fallback = {};

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

const getCsrfToken = () => {
    // First try meta tag (Laravel blade)
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        return metaToken.getAttribute("content");
    }
    // Fallback to XSRF-TOKEN cookie
    const match = document.cookie
        .split(";")
        .map((c) => c.trim())
        .find((c) => c.startsWith("XSRF-TOKEN="));
    return match ? decodeURIComponent(match.split("=")[1]) : "";
};

// Handle name input - only allow letters (Arabic and English) and spaces
function handleNameInput(event) {
    const input = event.target;
    // Remove anything that's not Arabic letters, English letters, or spaces
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

// Toast notification function
const showToast = (text, type = "success") => {
    const toast = document.getElementById("toast");
    const toastMessage = document.getElementById("toastMessage");
    if (!toast || !toastMessage) return;

    toastMessage.textContent = text;
    toast.className = "toast";
    if (type === "error") {
        toast.classList.add("error");
    }

    // Show the toast
    setTimeout(() => {
        toast.classList.add("show");
    }, 10);

    // Hide after 3 seconds (if not redirecting)
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
    } catch (err) {
        populateOptions(select, fallbackData, labelKey);
    }
}

async function loadAdministratives() {
    await loadOptions(administrativeSelect, endpoints.administratives, []);
    populateOptions(departmentSelect, []);
    populateOptions(sectionSelect, []);
}

if (governorateSelect) {
    governorateSelect.addEventListener("change", () => {
        setMessage("");
    });
}

administrativeSelect.addEventListener("change", (e) => {
    const adminId = e.target.value;
    loadOptions(departmentSelect, endpoints.departments(adminId), [], "name");
    populateOptions(sectionSelect, []);
});

institutionSelect.addEventListener("change", (e) => {
    const instId = e.target.value;
    loadOptions(majorSelect, endpoints.majors(instId), [], "name");
});

departmentSelect.addEventListener("change", (e) => {
    const deptId = e.target.value;
    const adminId = administrativeSelect.value;
    loadOptions(sectionSelect, endpoints.sections(deptId, adminId), [], "name");
});
majorSelect.addEventListener("change", async (e) => {
    try {
        const res = await fetch(endpoints.majorColleges(e.target.value));
        if (!res.ok) return;
        const data = await res.json();
        if (Array.isArray(data) && data.length > 0) {
            const first = data[0];
            if (first.institution_id && institutionSelect) {
                institutionSelect.value = first.institution_id;
            }
            // set hidden college_id field to the first linked college id
            const collegeInput = document.getElementById("college_id");
            if (collegeInput) {
                collegeInput.value = first.id ?? "";
            }
        } else {
            const collegeInput = document.getElementById("college_id");
            if (collegeInput) collegeInput.value = "";
        }
    } catch (err) {
        const collegeInput = document.getElementById("college_id");
        if (collegeInput) collegeInput.value = "";
    }
});

// Handle DOB dropdowns - combine to dd/mm/yyyy format for the hidden field
function updateDobHiddenField() {
    if (dobDay && dobMonth && dobYear && dobInput) {
        const day = dobDay.value;
        const month = dobMonth.value;
        const year = dobYear.value;
        if (day && month && year) {
            dobInput.value = `${day}/${month}/${year}`;
        } else {
            dobInput.value = "";
        }
    }
}

if (dobDay) dobDay.addEventListener("change", updateDobHiddenField);
if (dobMonth) dobMonth.addEventListener("change", updateDobHiddenField);
if (dobYear) dobYear.addEventListener("change", updateDobHiddenField);

// Handle full name input - only allow letters
if (fullNameInput) {
    fullNameInput.addEventListener("input", handleNameInput);
}

// File preview handler
const letterFileInput = document.getElementById("letter_file");
const filePreview = document.getElementById("file_preview");
const previewImage = document.getElementById("preview_image");
const previewPdf = document.getElementById("preview_pdf");
const previewPdfName = document.getElementById("preview_pdf_name");

if (letterFileInput) {
    letterFileInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (!file) {
            filePreview.style.display = "none";
            return;
        }

        filePreview.style.display = "block";

        if (file.type.startsWith("image/")) {
            // Show image preview
            previewImage.style.display = "block";
            previewPdf.style.display = "none";
            const reader = new FileReader();
            reader.onload = (event) => {
                previewImage.src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else if (file.type === "application/pdf") {
            // Show PDF icon with filename
            previewImage.style.display = "none";
            previewPdf.style.display = "flex";
            previewPdf.style.alignItems = "center";
            previewPdf.style.gap = "8px";
            previewPdfName.textContent = file.name;
        } else {
            filePreview.style.display = "none";
        }
    });
}

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    setMessage("جاري الإرسال...", "note");
    // Ensure college_id is set. If missing, try to fetch from major-colleges endpoint.
    const collegeInput = document.getElementById("college_id");
    const majorId = majorSelect ? majorSelect.value : null;
    if (collegeInput && (!collegeInput.value || collegeInput.value === "")) {
        if (majorId) {
            try {
                const res = await fetch(endpoints.majorColleges(majorId));
                if (res.ok) {
                    const data = await res.json();
                    if (Array.isArray(data) && data.length > 0) {
                        collegeInput.value = data[0].id ?? "";
                        if (data[0].institution_id && institutionSelect) {
                            institutionSelect.value = data[0].institution_id;
                        }
                    }
                }
            } catch (err) {
                // ignore and continue; server has a fallback too
                console.warn(
                    "Could not fetch college for major before submit",
                    err
                );
            }
        }
    }

    const formData = new FormData(form);
    // status is set by server to STATUS_NEW (1)

    // Debug: log key fields
    try {
        console.log("Submitting form", {
            national_id: formData.get("national_id"),
            full_name: formData.get("full_name"),
            major_id: formData.get("major_id"),
            college_id: formData.get("college_id"),
            institution_id: formData.get("institution_id"),
        });
    } catch (err) {}

    try {
        const res = await fetch(endpoints.submit, {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!res.ok) {
            const bodyText = await res.text();
            console.error("Submission failed", res.status, bodyText);
            throw new Error("Submission failed");
        }

        const data = await res.json();
        setMessage("", "note"); // Clear the inline message
        showToast("تم إرسال الطلب بنجاح. جاري التحويل...", "success");
        form.reset();

        // Redirect to welcome page after successful submission
        setTimeout(() => {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = "/WelcomeForm";
            }
        }, 2000);
    } catch (err) {
        showToast("تعذر الإرسال، جرّب لاحقاً.", "error");
    }
});

(async function init() {
    // governorateSelect is already populated by Blade @foreach
    await loadOptions(
        institutionSelect,
        endpoints.institutions,
        fallback.institutions
    );
    await loadOptions(
        trainingTypeSelect,
        endpoints.trainingFocus,
        fallback.trainingFocus
    );
    populateOptions(sectionSelect, []);
    loadAdministratives();
    // Add smooth entrance animation to the form
    const heroElement = document.querySelector(".hero");
    const formCard = document.querySelector(".card");

    if (heroElement) {
        heroElement.style.opacity = "0";
        heroElement.style.transform = "translateY(-20px)";

        setTimeout(() => {
            heroElement.style.transition =
                "opacity 0.5s ease, transform 0.5s ease";
            heroElement.style.opacity = "1";
            heroElement.style.transform = "translateY(0)";
        }, 100);
    }

    if (formCard) {
        formCard.style.opacity = "0";
        formCard.style.transform = "translateY(20px)";

        setTimeout(() => {
            formCard.style.transition =
                "opacity 0.6s ease, transform 0.6s ease";
            formCard.style.opacity = "1";
            formCard.style.transform = "translateY(0)";
        }, 300);
    }
})();
