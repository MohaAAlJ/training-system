// الإعدادات: حالياً تشير لملفات JSON محلية داخل مجلد النموذج
const endpoints = {
    addresses: "/admin/form/api/addresses",
    institutions: "/admin/form/api/institutions",
    majors: (institutionId) =>
        `/admin/form/api/majors?institution_id=${institutionId ?? ""}`,
    trainingFocus: "/admin/form/api/training-types",
    administratives: "/admin/form/api/administratives",
    departments: (administrativeId, majorId) =>
        `/admin/form/api/departments?administrative_id=${
            administrativeId ?? ""
        }&major_id=${majorId ?? ""}`,
    submit: "/admin/form",
};

// بيانات بديلة مؤقتة (أزلها عند توفر الـ API)
const fallback = {
    addresses: [
        { id: "riyadh", name: "الرياض" },
        { id: "jeddah", name: "جدة" },
        { id: "dammam", name: "الدمام" },
    ],
    institutions: [
        { id: 1, name: "جامعة الملك سعود" },
        { id: 2, name: "جامعة الملك عبدالعزيز" },
    ],
    majors: [
        { id: 1, name: "التمريض", institution_id: 1 },
        { id: 2, name: "تقنية المعلومات", institution_id: 1 },
        { id: 3, name: "الأشعة", institution_id: 2 },
    ],
    trainingFocus: [
        { id: "Uni", name: "تدريب جامعي" },
        { id: "minis", name: "مزاولة مهنة" },
    ],
    administratives: [
        { id: 10, name: "مديرية الخدمات الطبية", remaining: 5 },
        { id: 11, name: "مديرية تقنية المعلومات", remaining: 0 },
        { id: 12, name: "مديرية التمريض", remaining: 2 },
    ],
    departments: [
        {
            id: 100,
            name: "قسم الطوارئ - المبنى الرئيسي",
            administrative_id: 10,
            major_ids: [1],
            remaining: 3,
        },
        {
            id: 101,
            name: "قسم العناية المركزة - الطابق الثاني",
            administrative_id: 10,
            major_ids: [1],
            remaining: 0,
        },
        {
            id: 102,
            name: "قسم التطبيقات - تقنية المعلومات",
            administrative_id: 11,
            major_ids: [2],
            remaining: 0,
        },
        {
            id: 103,
            name: "قسم الأشعة - الدور الأرضي",
            administrative_id: 12,
            major_ids: [3],
            remaining: 1,
        },
    ],
};

const form = document.getElementById("applicationForm");
const message = document.getElementById("formMessage");
const addressSelect = document.getElementById("address");
const institutionSelect = document.getElementById("institution_id");
const majorSelect = document.getElementById("major_id");
const administrativeSelect = document.getElementById("administrative_id");
const departmentSelect = document.getElementById("department_id");
const trainingTypeSelect = document.getElementById("training_type");
const dobInput = document.getElementById("dob");

const getCsrfToken = () => {
    const match = document.cookie
        .split(";")
        .map((c) => c.trim())
        .find((c) => c.startsWith("XSRF-TOKEN="));
    return match ? decodeURIComponent(match.split("=")[1]) : "";
};

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
    await loadOptions(
        administrativeSelect,
        endpoints.administratives,
        fallback.administratives
    );
    populateOptions(departmentSelect, []);
}

async function filterDepartments(adminId, majorId) {
    try {
        const res = await fetch(endpoints.departments(adminId, majorId));
        if (!res.ok) throw new Error("Request failed");
        const data = await res.json();
        populateOptions(departmentSelect, data);
    } catch (err) {
        const data = fallback.departments.filter(
            (d) =>
                (!adminId || d.administrative_id == adminId) &&
                (!majorId || d.major_ids.includes(Number(majorId)))
        );
        populateOptions(departmentSelect, data);
    }
}

addressSelect.addEventListener("change", () => {
    setMessage("");
});

institutionSelect.addEventListener("change", (e) => {
    const instId = e.target.value;
    const data = fallback.majors.filter(
        (m) => !instId || m.institution_id == instId
    );
    populateOptions(majorSelect, data);
});

administrativeSelect.addEventListener("change", (e) => {
    filterDepartments(e.target.value, majorSelect.value);
});

majorSelect.addEventListener("change", (e) => {
    filterDepartments(administrativeSelect.value, e.target.value);
});

if (dobInput) {
    dobInput.addEventListener("input", handleDobInput);
}

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    setMessage("جاري الإرسال...", "note");

    const formData = new FormData(form);
    formData.append("status", "pending");

    try {
        const res = await fetch(endpoints.submit, {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!res.ok) throw new Error("Submission failed");
        setMessage("تم إرسال الطلب بنجاح.", "success");
        form.reset();
    } catch (err) {
        setMessage("تعذر الإرسال، جرّب لاحقاً بعد ربط الـ API.", "error");
    }
});

(async function init() {
    await loadOptions(addressSelect, endpoints.addresses, fallback.addresses);
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
    populateOptions(majorSelect, fallback.majors);
    populateOptions(departmentSelect, []);
    loadAdministratives();
})();
