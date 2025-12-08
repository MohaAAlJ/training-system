// الإعدادات: حالياً تشير لملفات JSON محلية داخل مجلد النموذج
const endpoints = {
    addresses: "./api/addresses.json",
    institutions: "./api/institutions.json",
    majors: (institutionId) =>
        `./api/majors.json?institution_id=${institutionId ?? ""}`,
    trainingFocus: "./api/training-focuses.json",
    administratives: (address) =>
        `./api/administratives.json?address=${encodeURIComponent(
            address ?? ""
        )}`,
    departments: (administrativeId, majorId) =>
        `./api/departments.json?administrative_id=${
            administrativeId ?? ""
        }&major_id=${majorId ?? ""}`,
    submit: "/form/applications",
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
        { id: "summer", name: "تدريب صيفي" },
        { id: "coop", name: "تدريب تعاوني" },
        { id: "field", name: "تدريب ميداني" },
    ],
    administratives: [
        {
            id: 10,
            name: "مديرية الخدمات الطبية",
            address: "riyadh",
            remaining: 5,
        },
        {
            id: 11,
            name: "مديرية تقنية المعلومات",
            address: "riyadh",
            remaining: 0,
        },
        { id: 12, name: "مديرية التمريض", address: "jeddah", remaining: 2 },
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
const focusSelect = document.getElementById("training_focus");
const administrativeSelect = document.getElementById("administrative_id");
const departmentSelect = document.getElementById("department_id");
const trainingTypeSelect = document.getElementById("training_type");

// خيارات نوع التدريب بحسب التخصص (يمكن التعديل أو الإضافة بحسب الحاجة)
// IDs per fallback majors: 1=تمريض، 2=تقنية المعلومات، 3=الأشعة
const trainingTypeByMajor = {
    1: [
        { id: "clinical", name: "تمريض سريري" },
        { id: "icu", name: "عناية مركزة" },
        { id: "er", name: "طوارئ" },
        { id: "vaccination", name: "تطعيم/حقن" },
        { id: "ward", name: "أجنحة وتنظيم جرعات" },
    ],
    2: [
        { id: "networks", name: "شبكات" },
        { id: "software", name: "برمجة وتطوير" },
        { id: "security", name: "أمن سيبراني" },
        { id: "support", name: "دعم فني" },
        { id: "db", name: "قواعد بيانات" },
    ],
    3: [
        { id: "imaging", name: "تصوير شعاعي" },
        { id: "ct", name: "أشعة مقطعية" },
        { id: "mri", name: "أشعة رنين" },
        { id: "xray", name: "أشعة سينية" },
    ],
    default: [],
};

function setTrainingTypeOptions(majorId) {
    if (!trainingTypeSelect) return;
    if (!majorId) {
        populateOptions(trainingTypeSelect, []);
        return;
    }
    const options = trainingTypeByMajor[majorId] || trainingTypeByMajor.default;
    populateOptions(trainingTypeSelect, options);
}

const setMessage = (text, type = "note") => {
    message.textContent = text;
    message.className =
        type === "error" ? "error" : type === "success" ? "success" : "note";
};

const populateOptions = (select, items, labelKey = "name") => {
    select.innerHTML = '<option value="" disabled selected>اختر</option>';
    items.forEach((item) => {
        const opt = document.createElement("option");
        opt.value = item.id;
        opt.textContent = item[labelKey] ?? "";
        select.appendChild(opt);
    });
};

async function loadOptions(select, url, fallbackData, labelKey = "name") {
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error("Request failed");
        const data = await res.json();
        populateOptions(select, data, labelKey);
    } catch (err) {
        populateOptions(select, fallbackData, labelKey);
    }
}

function filterAdministrativesByAddress(addressId) {
    const data = fallback.administratives.filter(
        (a) => (!addressId || a.address === addressId) && a.remaining > 0
    );
    populateOptions(
        administrativeSelect,
        data.map((a) => ({
            id: a.id,
            name: a.name,
        }))
    );
    populateOptions(departmentSelect, []);
}

function filterDepartments(adminId, majorId) {
    const data = fallback.departments.filter(
        (d) =>
            (!adminId || d.administrative_id == adminId) &&
            (!majorId || d.major_ids.includes(Number(majorId))) &&
            d.remaining > 0
    );
    populateOptions(
        departmentSelect,
        data.map((d) => ({ id: d.id, name: d.name }))
    );
}

addressSelect.addEventListener("change", (e) => {
    filterAdministrativesByAddress(e.target.value);
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
    setTrainingTypeOptions(e.target.value);
});

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
                // أضف رمز CSRF عند توفره
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
        focusSelect,
        endpoints.trainingFocus,
        fallback.trainingFocus
    );
    populateOptions(majorSelect, fallback.majors);
    populateOptions(
        administrativeSelect,
        fallback.administratives
            .filter((a) => a.remaining > 0)
            .map((a) => ({
                id: a.id,
                name: a.name,
            }))
    );
    populateOptions(departmentSelect, []);
    setTrainingTypeOptions();
})();
