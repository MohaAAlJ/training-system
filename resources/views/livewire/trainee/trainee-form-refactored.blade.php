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
                <p class="eyebrow">بوابة التدريب </p>
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
                <div class="hero__brand-meta">
                    <span class="hero__brand-name">UCAD | PRCS</span>
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

                <!-- FIELDSET 1: Training Type & National ID -->
                <x-form.fieldset-training-type :$trainingTypes :$nationalIdReadonly />

                <!-- FIELDSET 2: Personal Details -->
                <x-form.fieldset-personal-details :$showPersonalDetails :$governorates :$fullNameReadonly :$dobReadonly />

                <!-- FIELDSET 3: Training Details -->
                <x-form.fieldset-training-details :$showPersonalDetails :$institutions :$allMajors :$administratives :$allSections :$allDepartments :$trainingType />

                <!-- Terms & Conditions Section -->
                <x-form.form-terms :$showPersonalDetails />

                <!-- Submit Button -->
                <x-form.form-footer :$showPersonalDetails :$isValidating :$termsApproval />
            </form>

            <!-- Toast container -->
            <div id="toast" class="toast">
                <span id="toastMessage"></span>
            </div>
        </div>
    </main>
</div>

@vite(['resources/css/components/trainee-form.css'])

@script
<script type="module" src="{{ asset('js/components/trainee-form.js') }}"></script>
@endscript
