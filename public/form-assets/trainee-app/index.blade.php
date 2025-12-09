<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>طلب تدريب المتدرب</title>
        <link rel="stylesheet" href="/Form/trainee-app/styles.css" />
    </head>
    <body>
        <main class="page">
            <!-- <div class="brand-bar">
                <div class="brand-bar__left">
                    <div class="brand-bar__logo">
                        <img src="/Form/trainee-app/logo.png" alt="شعار PRICs" />
                    </div>
                    <div class="brand-bar__meta">
                        <span class="brand-bar__label">PRICs</span>
                        <span class="brand-bar__sub"
                            >نظام التدريب التعاوني</span
                        >
                    </div>
                </div>
                <div class="brand-bar__pill">صفحة طلب التدريب</div>
            </div> -->
            <header class="hero hero--banner">
                <div class="hero__text">
                    <p class="eyebrow">بوابة التدريب التعاوني</p>
                    <h1>طلب تدريب المتدرب</h1>
                    <p class="lead">
                        أكمل بياناتك لاختيار الجهة والتخصص والقسم المناسب وفق
                        الطاقة الاستيعابية المتاحة.
                    </p>
                </div>
                <div class="hero__brand">
                    <div class="hero__logo">
                        <img
                            src="/Form/trainee-app/logo.png"
                            alt="شعار PRICs"
                        />
                    </div>
                    <div class="hero__brand-meta">
                        <span class="hero__brand-name">PRCS</span>
                        <span class="hero__brand-sub"> </span>
                    </div>
                </div>
            </header>

            <form
                id="applicationForm"
                class="card"
                method="post"
                action="/admin/form"
                enctype="multipart/form-data"
            >
                @csrf
                <fieldset>
                    <legend>
                        <span class="legend-icon">👤</span>البيانات الشخصية
                    </legend>
                    <div class="grid two">
                        <label class="field">
                            <span>الاسم الكامل *</span>
                            <input
                                id="full_name"
                                name="full_name"
                                type="text"
                                pattern="[A-Za-z\u0600-\u06FF\s]+"
                                title="أدخل حروفاً فقط"
                                required
                                autocomplete="name"
                            />
                        </label>
                        <label class="field">
                            <span>تاريخ الميلاد *</span>
                            <input
                                id="dob"
                                name="dob"
                                type="text"
                                dir="ltr"
                                inputmode="numeric"
                                placeholder="dd/mm/yyyy"
                                pattern="\d{2}/\d{2}/\d{4}"
                                title="صيغة التاريخ dd/mm/yyyy"
                            />
                        </label>
                        <label class="field">
                            <span>رقم الهوية *</span>
                            <input
                                id="national_id"
                                name="national_id"
                                type="text"
                                minlength="9"
                                maxlength="9"
                                pattern="\d{9}"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                required
                            />
                            <small class="note">9 أرقام</small>
                        </label>
                        <label class="field">
                            <span>رقم الجوال *</span>
                            <input
                                id="phone_number"
                                name="phone_number"
                                type="tel"
                                pattern="^97\d5\d{8}$"
                                inputmode="numeric"
                                maxlength="12"
                                placeholder="97x5xxxxxxxx"
                                title="ابدأ بـ97 ثم رقم واحد ثم 5 ثم 8 أرقام"
                                required
                            />
                        </label>
                        <label class="field">
                            <span>العنوان *</span>
                            <select id="address" name="address" required>
                                <option value="" disabled selected>
                                    اختر العنوان
                                </option>
                            </select>
                            <small class="note">المحافظة .</small>
                        </label>
                        <label class="field">
                            <span>اسم الشارع *</span>
                            <input
                                id="street"
                                name="street"
                                type="text"
                                placeholder="اسم الشارع الذي تسكن فيه"
                                required
                            />
                        </label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>
                        <span class="legend-icon">🏢</span>الجهة والتدريب
                    </legend>
                    <div class="grid two">
                        <label class="field">
                            <span>جامعة/كلية *</span>
                            <select
                                id="institution_id"
                                name="institution_id"
                                required
                            >
                                <option value="" disabled selected>
                                    اختر الجهة التعليمية
                                </option>
                            </select>

                            <small class="note"></small>
                        </label>
                        <label class="field">
                            <span>التخصص *</span>
                            <select id="major_id" name="major_id" required>
                                <option value="" disabled selected>
                                    اختر التخصص
                                </option>
                            </select>
                            <small class="note"> </small>
                        </label>
                        <label class="field">
                            <span>عدد ساعات التدريب *</span>
                            <input
                                id="training_hours"
                                name="training_hours"
                                type="number"
                                min="1"
                                max="1000"
                                step="1"
                                inputmode="numeric"
                                required
                            />
                        </label>
                        <!-- <label class="field">
                            <span>نوع التدريب *</span>
                            <select
                                id="training_type"
                                name="training_type"
                                required
                            >
                                <option value="" disabled selected>
                                    اختر نوع التدريب
                                </option>
                            </select>
                            <small class="note">من واجهة البيانات.</small>
                        </label> -->
                        <label class="field">
                            <span>الدوائر *</span>
                            <select
                                id="administrative_id"
                                name="administrative_id"
                                required
                            >
                                <option value="" disabled selected>
                                    اختر الدائرة
                                </option>
                            </select>
                            <small class="note"> </small>
                        </label>
                        <label class="field">
                            <span>القسم *</span>
                            <select
                                id="department_id"
                                name="department_id"
                                required
                            >
                                <option value="" disabled selected>
                                    اختر القسم
                                </option>
                            </select>
                            <small class="note"> </small>
                        </label>
                        <label class="field" id="training_type_field">
                            <span>نوع التدريب *</span>
                            <select
                                id="training_type"
                                name="training_type"
                                required
                            >
                                <option value="" disabled selected>
                                    اختر نوع التدريب
                                </option>
                            </select>
                        </label>
                        <label class="field">
                            <span>ارفع ملف <small class="note">(اختياري)</small></span>
                            <input
                                id="letter_file"
                                name="letter_file"
                                type="file"
                                accept="image/*,application/pdf"
                            />
                            <small class="note">pdf أو صورة بحد أقصى 2MB</small>
                        </label>
                    </div>
                </fieldset>

                <div class="form-footer">
                    <button type="submit">إرسال الطلب</button>
                </div>
                <div
                    id="formMessage"
                    class="note"
                    style="margin-top: 10px"
                ></div>
            </form>
        </main>

        <script src="/Form/trainee-app/app.js" defer></script>
    </body>
</html>
