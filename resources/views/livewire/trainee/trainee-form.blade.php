<div class="app-container">
    <div class="theme-switch">
        <button id="themeToggle" type="button" title="تبديل الوضع">
            <span class="mode-icon">🌓</span>
        </button>
    </div>

    <main class="page">
        <!-- Hero Section -->
        <header class="hero hero--banner">
            <div class="hero__text">
                <p class="eyebrow">بوابة التدريب</p>
                <h1>طلب تدريب المتدرب</h1>
                <p class="lead">
                    أكمل بياناتك لاختيار الجهة والتخصص والقسم المناسب وفق
                    الطاقة الاستيعابية المتاحة.
                </p>
            </div>
            <div class="hero__brand">
                <div class="hero__logos">
                    <div class="hero__logo">
                        <img src="{{ asset('form-assets/trainee-app/logo.png') }}" alt="PRCS" />
                    </div>
                    <div class="hero__logo">
                        <img src="{{ asset('favicon.ico') }}" alt="UCAD" />
                    </div>
                </div>
            </div>
        </header>

        <!-- Application Status Message - Hidden Inline Notification for Toast -->
        <div wire:key="status-message" style="display: none;">
            @if ($statusMessage)
                <div class="inline-notification inline-notification--{{ $statusMessageType }}" wire:transition>
                    {{ $statusMessage }}
                </div>
            @endif
        </div>

        <div class="card-wrapper">
            <form wire:submit.prevent="submit" id="applicationForm" class="card">
                <!-- ERROR FIELDSET: Application Status Error (Hidden by default) -->
                @if ($statusMessage && $statusMessageType === 'error')
                <fieldset class="fieldset fieldset--error" wire:transition>
                    <legend>
                        <span class="legend-icon">⚠️</span>تنبيه مهم
                    </legend>
                    <div class="error-message">
                        <p style="color: #d32f2f; font-weight: 600; font-size: 16px; margin: 0; text-align: right;">
                            {{ $statusMessage }}
                        </p>
                    </div>
                </fieldset>
                @endif

                <!-- FIELDSET 1: Application Check -->
                <fieldset class="fieldset">
                    <legend>
                        <span class="legend-icon">🔍</span>التحقق من الطلب
                    </legend>
                    <div class="grid three">
                        <label class="field">
                            <span>نوع التدريب *</span>
                            <select wire:model.live="trainingType" class="form__input" required>
                                <option value="">-- اختر نوع التدريب --</option>
                                <option value="1">تدريب جامعي</option>
                                <option value="2">تدريب عملي</option>
                            </select>
                            @error('trainingType') <span class="form__error">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>رقم الهوية الوطنية *</span>
                            <input 
                                type="text" 
                                wire:model.live="nationalId" 
                                placeholder="9 أرقام"
                                maxlength="9"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                class="form__input"
                                required
                            >
                            <small class="note">9 أرقام فقط</small>
                        </label>
                    </div>
                </fieldset>

                <!-- FIELDSET 2: Personal Details (Hidden by default) -->
                @if ($showPersonalDetails)
                <fieldset class="fieldset">
                    <legend>
                        <span class="legend-icon">👤</span>البيانات الشخصية
                    </legend>
                    <div class="grid three">
                        <label class="field">
                            <span>الاسم الكامل *</span>
                            <input 
                                type="text" 
                                wire:model.live="fullName" 
                                placeholder="حروف فقط"
                                maxlength="150"
                                class="form__input"
                                required
                            >
                            @error('fullName') <span class="form__error">{{ $message }}</span> @enderror
                            <small class="note">حروف فقط - بحد أقصى 150 حرف</small>
                        </label>

                        <!-- Date of Birth Picker Component -->
                        <livewire:trainee.date-of-birth-picker :key="'dob-' . $formUuid" />

                        <label class="field">
                            <span>المحافظة / المنطقة *</span>
                            <select wire:model.live="governorateId" class="form__input" required>
                                <option value="">-- اختر المحافظة --</option>
                                @foreach ($governorates as $gov)
                                    <option value="{{ $gov['id'] }}">{{ $gov['name'] }}</option>
                                @endforeach
                            </select>
                            @error('governorateId') <span class="form__error">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>الشارع / الحي *</span>
                            <input 
                                type="text" 
                                wire:model.live="street" 
                                placeholder="أدخل الشارع أو الحي"
                                class="form__input"
                                required
                            >
                            @error('street') <span class="form__error">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>رقم الهاتف *</span>
                            <input 
                                type="tel" 
                                wire:model.live="phoneNumber" 
                                placeholder="مثال: 970591234567"
                                class="form__input"
                                required
                            >
                            @error('phoneNumber') <span class="form__error">{{ $message }}</span> @enderror
                            <small class="note">صيغة: 97x5xxxxxxxx أو 97x56xxxxxxx</small>
                        </label>
                    </div>
                </fieldset>

                <!-- FIELDSET 3: Training Details (Hidden until fieldset 2 is valid) -->
                @if ($showPersonalDetails)
                <fieldset class="fieldset">
                    <legend>
                        <span class="legend-icon">🏢</span>بيانات التدريب
                    </legend>
                    <div class="grid three">
                        <label class="field">
                            <span>عدد ساعات التدريب *</span>
                            <input 
                                type="number" 
                                wire:model.live="trainingHours" 
                                placeholder="50 - 1000 ساعة"
                                min="50"
                                max="1000"
                                class="form__input"
                                required
                            >
                            @error('trainingHours') <span class="form__error">{{ $message }}</span> @enderror
                            <small class="note">الحد الأدنى: 50 ساعة، الحد الأقصى: 1000 ساعة</small>
                        </label>

                        <!-- University-specific fields -->
                        @if ($trainingType === 1)
                            <label class="field">
                                <span>المؤسسة الأكاديمية *</span>
                                <select wire:model.live="institutionId" class="form__input" required>
                                    <option value="">-- اختر الجامعة --</option>
                                    @foreach ($institutions as $inst)
                                        <option value="{{ $inst['id'] }}">{{ $inst['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('institutionId') <span class="form__error">{{ $message }}</span> @enderror
                            </label>

                            <label class="field">
                                <span>التخصص *</span>
                                <select wire:model.live="majorId" class="form__input" required>
                                    <option value="">-- اختر التخصص --</option>
                                    @foreach ($majors as $major)
                                        <option value="{{ $major['id'] }}">{{ $major['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('majorId') <span class="form__error">{{ $message }}</span> @enderror
                            </label>
                        @endif

                        <label class="field">
                            <span>الجهة الحكومية *</span>
                            <select wire:model.live="administrativeId" class="form__input" required>
                                <option value="">-- اختر الجهة --</option>
                                @foreach ($administratives as $admin)
                                    <option value="{{ $admin['id'] }}">{{ $admin['name'] }}</option>
                                @endforeach
                            </select>
                            @error('administrativeId') <span class="form__error">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>القسم *</span>
                            <select wire:model.live="departmentId" class="form__input" required>
                                <option value="">-- اختر القسم --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                                @endforeach
                            </select>
                            @error('departmentId') <span class="form__error">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>الفئة / القطاع *</span>
                            <select wire:model.live="sectionId" class="form__input" required>
                                <option value="">-- اختر الفئة --</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                @endforeach
                            </select>
                            @error('sectionId') <span class="form__error">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </fieldset>

                <!-- Terms & Conditions Section -->
                <div class="terms-section">
                    <label class="checkbox-field">
                        <input 
                            type="checkbox" 
                            wire:model.live="termsApproval"
                            class="form__checkbox"
                        >
                        <div class="checkbox-content">
                            <p class="checkbox-title">أوافق على شروط البرنامج التدريبي</p>
                            <p class="checkbox-description">أؤكد أن البيانات المدخلة صحيحة وأتحمل مسؤولية أي معلومات خاطئة</p>
                        </div>
                    </label>
                    @error('termsApproval') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <!-- Submit Button -->
                <div class="form-footer">
                    <button 
                        type="submit" 
                        id="submitBtn"
                        class="glow-button"
                        :disabled="!$termsApproval || $isValidating"
                    >
                        @if ($isValidating)
                            <span class="spinner"></span> جاري الفحص...
                        @else
                            إرسال الطلب
                        @endif
                    </button>
                </div>
                @endif
                @endif
            </form>
        </div>

        <!-- Toast container -->
        <div id="toast" class="toast">
            <span id="toastMessage"></span>
        </div>

        <style>
            /* Hero Section Spacing */
            .hero {
                margin-top: 20px;
            }

            /* Error Fieldset Styling - Light Theme (Default) */
            .fieldset--error {
                background-color: #ffebee;
                border: 2px solid #d32f2f;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }

            .fieldset--error {
                color: #d32f2f;
                font-weight: 700;
                font-size: 18px;
                margin-bottom: 15px;
            }

            .fieldset--error .legend-icon {
                font-size: 24px;
                margin-left: 8px;
            }

            .error-message {
                padding: 15px;
                background-color: #fff;
                border-left: 4px solid #d32f2f;
                border-radius: 4px;
            }

            .error-message p {
                color: #d32f2f;
                line-height: 1.6;
                margin-bottom: 8px;
            }

            .error-message p:last-child {
                margin-bottom: 0;
            }

            /* Error Fieldset Styling - Dark Theme */
            [data-theme="dark"] .fieldset--error {
                background-color: #4a1f1f;
                border: 2px solid #ff6b6b;
            }

            [data-theme="dark"] .fieldset--error legend {
                color: #ff6b6b;
            }

            [data-theme="dark"] .error-message {
                background-color: #2a2a2a;
                border-left-color: #ff6b6b;
            }

            [data-theme="dark"] .error-message p {
                color: #ff6b6b;
            }
        </style>
    </main>
</div>

@script
<script>
    // Initialize theme toggle
    document.addEventListener('livewire:initialized', () => {
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        themeToggle?.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // National ID - Only digits
        const nationalIdInput = document.getElementById('national_id');
        if (nationalIdInput) {
            nationalIdInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
            nationalIdInput.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        }

        // Sanitize name input - only letters and spaces
        const fullNameInput = document.getElementById('full_name');
        if (fullNameInput) {
            fullNameInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^A-Za-z\u0600-\u06FF\s]/g, '');
            });
        }

        // Phone number - Only digits
        const phoneInput = document.getElementById('phone_number');
        if (phoneInput) {
            phoneInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
            phoneInput.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        }
    });

    // Handle toast notifications
    document.addEventListener('livewire:navigated', () => {
        Livewire.on('show-toast', ({ message, type }) => {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            if (toast && toastMessage) {
                toastMessage.textContent = message;
                toast.classList.remove('show', 'error', 'success', 'warning', 'info');
                toast.classList.add('show', type || 'success');
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 4000);
            }
        });
    });
</script>
@endscript
