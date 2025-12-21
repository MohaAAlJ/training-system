<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>طلب تدريب المتدرب</title>
        <link rel="stylesheet" href="{{ asset('form-assets/trainee-app/styles.css') }}" />
    </head>
    <body>
        <main class="page">
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
                            src="{{ asset('form-assets/trainee-app/logo.png') }}"
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
                action="{{ route('training.form.store') }}"
                enctype="multipart/form-data"
            >
                @csrf
                <fieldset>
                    <legend>
                        <span class="legend-icon">👤</span>البيانات الشخصية
                    </legend>
                    <div class="grid two">
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
                            <span>الاسم الكامل *</span>
                            <input
                                id="full_name"
                                name="full_name"
                                type="text"
                                maxlength="255"
                                pattern="^[A-Za-z\u0600-\u06FF\s]+$"
                                title="أدخل حروفاً فقط"
                                required
                                autocomplete="name"
                            />
                            <small class="note">حروف فقط.</small>
                        </label>
                        <label class="field">
                            <span>تاريخ الميلاد *</span>
                            {{-- Option 1: Native date picker (calendar with scroll wheel for year)
                            <input
                                id="dob_picker"
                                type="date"
                                min="{{ date('Y-m-d', strtotime('-60 years')) }}"
                                max="{{ date('Y-m-d', strtotime('-20 years')) }}"
                                required
                            />
                            --}}
                            {{-- Option 2: Dropdown selects for day/month/year --}}
                            <div class="dob-selects" style="display: flex; gap: 8px;">
                                <select id="dob_day" required style="flex: 1; text-align: center;">
                                    <option value="" disabled selected>اليوم</option>
                                    @for ($d = 1; $d <= 31; $d++)
                                        <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ $d }}</option>
                                    @endfor
                                </select>
                                <select id="dob_month" required style="flex: 1; text-align: center;">
                                    <option value="" disabled selected>الشهر</option>
                                    @php
                                        $months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                                    @endphp
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ $months[$m - 1] }}</option>
                                    @endfor
                                </select>
                                <select id="dob_year" required style="flex: 1; text-align: center;">
                                    <option value="" disabled selected>السنة</option>
                                    @for ($y = date('Y') - 20; $y >= date('Y') - 60; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <input
                                id="dob"
                                name="dob"
                                type="hidden"
                            />
                            <small class="note"></small>
                        </label>

                        <label class="field">
                            <span>رقم الجوال *</span>
                            <input
                                id="phone_number"
                                name="phone_number"
                                type="tel"
                                pattern="^97[02]5[69]\d{7}$"
                                inputmode="numeric"
                                minlength="12"
                                maxlength="12"
                                placeholder="97x5xxxxxxxx"
                                title="الصيغة: 9725 أو 9705 ثم 9 أو 6 ثم 7 أرقام"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                required
                            />
                            <small class="note">مثال: 970591234567 أو 972561234567</small>
                        </label>
                        <label class="field">
                            <span>المحافظة *</span>
                            <select id="governorate_id" name="governorate_id" required>
                                <option value="" disabled selected>اختر </option>
                                @foreach(\App\Models\Governorate::all() as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field">
                            <span>اسم الشارع *</span>
                            <input
                                id="street"
                                name="street"
                                type="text"
                                maxlength="255"
                                pattern="^[A-Za-z\u0600-\u06FF0-9\s\-\.,#\/]+$"
                                title="يمكن إدخال حروف وأرقام ورموز العنوان"
                                placeholder="مثال: شارع الملك فيصل 123"
                                required
                            />
                            <small class="note">حروف، أرقام، مسافات، - . , # /</small>
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
                                <option value="" disabled selected>اختر</option>
                            </select>

                            <small class="note"></small>
                        </label>
                        <label class="field">
                            <span>التخصص *</span>
                            <select id="major_id" name="major_id" required>
                                <option value="" disabled selected>اختر</option>
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
                                max="999"
                                step="1"
                                inputmode="numeric"
                                required
                            />
                            <small class="note">رقم موجب أقل من 1000</small>
                        </label>
                        <label class="field">
                            <span>المنشأة/الإدارة *</span>
                            <select
                                id="administrative_id"
                                name="administrative_id"
                                required
                            >
                                <option value="" disabled selected>اختر</option>
                            </select>
                            <small class="note"></small>
                        </label>
                        <label class="field">
                            <span>الدائرة *</span>
                            <select
                                id="department_id"
                                name="department_id"
                                required
                            >
                                <option value="" disabled selected>اختر</option>
                            </select>
                            <small class="note"></small>
                        </label>
                        <label class="field">
                            <span>القسم *</span>
                            <select
                                id="section_id"
                                name="section_id"
                                required
                            >
                                <option value="" disabled selected>اختر</option>
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
                                <option value="" disabled selected>اختر</option>
                            </select>
                        </label>
                        <!-- <label class="field">
                            <span>ارفع ملف <small class="note">(اختياري)</small></span>
                            <input
                                id="letter_file"
                                name="letter_file"
                                type="file"
                                accept="image/*,application/pdf"
                            />
                            <small class="note">pdf أو صورة بحد أقصى 2MB</small>
                            <div id="file_preview" style="margin-top: 10px; display: none;">
                                <img id="preview_image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid var(--border);" />
                                <div id="preview_pdf" style="display: none; padding: 12px; background: #f5f5f5; border-radius: 8px; border: 1px solid var(--border);">
                                    <span style="font-size: 24px;">📄</span>
                                    <span id="preview_pdf_name" style="margin-right: 8px;"></span>
                                </div>
                            </div>
                        </label> -->
                    </div>
                </fieldset>

                <div class="form-footer" style="justify-content: center;">
                    <button type="submit" class="glow-button">إرسال الطلب</button>
                </div>
                <div
                    id="formMessage"
                    class="note"
                    style="margin-top: 10px"
                ></div>
            </form>
        </main>

        <!-- Toast container -->
        <div id="toast" class="toast">
            <span id="toastMessage"></span>
        </div>

                <input type="hidden" id="college_id" name="college_id" />
        <script src="{{ asset('form-assets/trainee-app/app.js') }}" defer></script>
    </body>
</html>
