const endpoints = {
    address: "/WelcomeForm/Form/api/address",
    institution: "/WelcomeForm/Form/api/institution",
    major: (institutionId) =>
        `/WelcomeForm/Form/api/major?institution_id=${institutionId ?? ""}`,
    majorCollege: (majorId) =>
        `/WelcomeForm/Form/api/major-college?major_id=${majorId ?? ""}`,
    trainingFocus: "/WelcomeForm/Form/api/training-type",
    administrative: (trainingType) =>
        `/WelcomeForm/Form/api/administrative?training_type=${
            trainingType ?? ""
        }`,
    department: (adminId, trainingType) =>
        `/WelcomeForm/Form/api/department?administrative_id=${
            adminId ?? ""
        }&training_type=${trainingType ?? ""}`,
    section: (deptId, adminId, trainingType) =>
        `/WelcomeForm/Form/api/section?department_id=${
            deptId ?? ""
        }&administrative_id=${adminId ?? ""}&training_type=${
            trainingType ?? ""
        }`,
    submit: "/WelcomeForm/Form",
    checkNationalId: (nationalId) =>
        `/WelcomeForm/Form/api/check-national-id?national_id=${
            nationalId ?? ""
        }`,
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
const universityContainer = document.getElementById(
    "university_data_container"
);
const dobInput = document.getElementById("dob");
const dobDay = document.getElementById("dob_day");
const dobMonth = document.getElementById("dob_month");
const dobYear = document.getElementById("dob_year");
const fullNameInput = document.getElementById("full_name");
const nationalIdInput = document.getElementById("national_id");

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
        if (item.is_full) {
            opt.disabled = true;
            opt.style.color = "#999";
            opt.style.fontStyle = "italic";
            // Some browsers don't support style on options well, but disabled is standard
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

if (governorateSelect) {
    governorateSelect.addEventListener("change", () => {
        setMessage("");
    });
}

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

institutionSelect.addEventListener("change", (e) => {
    const instId = e.target.value;
    loadOptions(majorSelect, endpoints.major(instId), [], "name");
});

departmentSelect.addEventListener("change", (e) => {
    const deptId = e.target.value;
    const adminId = administrativeSelect.value;
    const trainingType = trainingTypeSelect ? trainingTypeSelect.value : "";
    loadOptions(
        sectionSelect,
        endpoints.section(deptId, adminId, trainingType),
        [],
        "name"
    );
});
majorSelect.addEventListener("change", async (e) => {
    try {
        const res = await fetch(endpoints.majorCollege(e.target.value));
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

if (trainingTypeSelect) {
    trainingTypeSelect.addEventListener("change", (e) => {
        const val = parseInt(e.target.value);
        // ID 1 is University (based on Constants::TRAINING_TYPE_UNIVERSITY)
        const isUniversity = val === 1;

        if (universityContainer) {
            universityContainer.style.display = isUniversity
                ? "contents"
                : "none";
        }

        if (institutionSelect) institutionSelect.required = isUniversity;
        if (majorSelect) majorSelect.required = isUniversity;

        if (!isUniversity) {
            if (institutionSelect) institutionSelect.value = "";
            if (majorSelect) majorSelect.value = "";
            const collegeInput = document.getElementById("college_id");
            if (collegeInput) collegeInput.value = "";
        }

        // Reload administratives based on selected training type
        loadAdministrative();
    });
}

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

// Check national ID uniqueness in real-time
if (nationalIdInput) {
    nationalIdInput.addEventListener("blur", async (e) => {
        const nationalId = e.target.value;
        if (nationalId.length === 9) {
            try {
                const res = await fetch(endpoints.checkNationalId(nationalId));
                if (res.ok) {
                    const data = await res.json();
                    if (data.exists) {
                        showToast(data.message, "error");
                        setMessage(data.message, "error");
                        nationalIdInput.classList.add("invalid");
                    } else {
                        nationalIdInput.classList.remove("invalid");
                        if (message.textContent === data.message) {
                            setMessage("");
                        }
                    }
                }
            } catch (err) {
                console.error("Error checking national ID", err);
            }
        }
    });
}

// File preview handler
const letterFileInput = document.getElementById("letter_file");
const filePreview = document.getElementById("file_preview");
const previewImage = document.getElementById("preview_image");
const previewPdf = document.getElementById("preview_pdf");
const previewPdfName = document.getElementById("preview_pdf_name");
const submitBtn = document.getElementById("submitBtn");
const termsCheckbox = document.getElementById("terms_approval");

if (termsCheckbox && submitBtn) {
    termsCheckbox.addEventListener("change", (e) => {
        submitBtn.disabled = !e.target.checked;
        if (e.target.checked) {
            submitBtn.style.opacity = "1";
            submitBtn.style.cursor = "pointer";
        } else {
            submitBtn.style.opacity = "0.5";
            submitBtn.style.cursor = "not-allowed";
        }
    });
}

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
                const res = await fetch(endpoints.majorCollege(majorId));
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
            const body = await res.json();
            if (res.status === 422 && body.errors) {
                // Handle validation errors specifically
                if (body.errors.national_id) {
                    showToast(body.errors.national_id[0], "error");
                    setMessage(body.errors.national_id[0], "error");
                } else {
                    const firstError = Object.values(body.errors)[0][0];
                    showToast(firstError, "error");
                    setMessage(firstError, "error");
                }
                return; // Stop execution
            }
            console.error("Submission failed", res.status, body);
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
        endpoints.institution,
        fallback.institutions
    );
    await loadOptions(
        trainingTypeSelect,
        endpoints.trainingFocus,
        fallback.trainingFocus
    );
    populateOptions(sectionSelect, []);
    loadAdministrative();
    // Start smooth entrance animation instantly
    const heroElement = document.querySelector(".hero");
    const formCard = document.querySelector(".card");

    if (heroElement) {
        heroElement.style.opacity = "0";
        heroElement.style.transform = "translateY(-20px)";

        requestAnimationFrame(() => {
            heroElement.style.transition =
                "opacity 0.5s ease, transform 0.5s ease";
            heroElement.style.opacity = "1";
            heroElement.style.transform = "translateY(0)";
        });
    }

    if (formCard) {
        formCard.style.opacity = "0";
        formCard.style.transform = "translateY(20px)";

        requestAnimationFrame(() => {
            formCard.style.transition =
                "opacity 0.6s ease, transform 0.6s ease";
            formCard.style.opacity = "1";
            formCard.style.transform = "translateY(0)";
        });
    }
})();
