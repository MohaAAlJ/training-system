<div class="app-container">
    <div class="theme-switch">
        <button id="themeToggle" type="button" title="تبديل الوضع" wire:click="toggleTheme">
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

                <!-- FIELDSET 2: Personal Details (shown conditionally) -->
                <x-form.fieldset-personal-details :$showPersonalDetails :$governorates :$fullNameReadonly
                    :$dobReadonly />

                <!-- FIELDSET 3: Training Details (shown conditionally) -->
                <x-form.fieldset-training-details :$showPersonalDetails :$institutions :$allMajors :$administratives
                    :$allSections :$allDepartments :$trainingType />

                <!-- Terms & Conditions Section (shown only when personal details are visible) -->
                <x-form.form-terms :$showPersonalDetails />

                <!-- Submit Button - Posts to ApplicationFormController::store -->
                <x-form.form-footer :$showPersonalDetails :$isValidating :$termsApproval />
            </form>

            <!-- Toast container -->
            <div id="toast" class="toast">
                <span id="toastMessage"></span>
            </div>
    </main>
</div>

@vite(['app/Livewire/Trainee/trainee-form.css'])

@script
<script>
    // Initialize theme toggle and input filters
    document.addEventListener('livewire:initialized', () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        // Setup input filtering for National ID - Only digits, no negative, no letters, no icons
        setupNationalIdFilter();
        setupPhoneFilter();

        // Listen for theme toggle events from Livewire
        Livewire.on('toggle-theme', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    });

    /**
     * Filter national ID input to accept only digits
     */
    function setupNationalIdFilter() {
        // Find the input by looking for the wire-model attribute
        const observer = new MutationObserver(() => {
            const nationalIdInputs = document.querySelectorAll('input[type="text"][pattern="[0-9]*"]');
            nationalIdInputs.forEach(input => {
                if (!input.dataset.filteredNationalId) {
                    input.dataset.filteredNationalId = 'true';

                    // Prevent non-digit input
                    input.addEventListener('input', (e) => {
                        e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    });

                    input.addEventListener('keypress', (e) => {
                        // Only allow digits 0-9
                        if (!/[0-9]/.test(e.key)) {
                            e.preventDefault();
                        }
                    });

                    // Prevent pasting non-digit content
                    input.addEventListener('paste', (e) => {
                        e.preventDefault();
                        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                        const digitsOnly = pastedText.replace(/[^0-9]/g, '');
                        e.target.value = digitsOnly;

                        // Trigger Livewire update
                        e.target.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                }
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    /**
     * Filter phone input to accept only digits
     */
    function setupPhoneFilter() {
        const observer = new MutationObserver(() => {
            const phoneInputs = document.querySelectorAll('input[type="tel"]');
            phoneInputs.forEach(input => {
                if (!input.dataset.filteredPhone) {
                    input.dataset.filteredPhone = 'true';

                    // Prevent non-digit input
                    input.addEventListener('input', (e) => {
                        e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    });

                    input.addEventListener('keypress', (e) => {
                        if (!/[0-9]/.test(e.key)) {
                            e.preventDefault();
                        }
                    });

                    // Prevent pasting non-digit content
                    input.addEventListener('paste', (e) => {
                        e.preventDefault();
                        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                        const digitsOnly = pastedText.replace(/[^0-9]/g, '');
                        e.target.value = digitsOnly;

                        // Trigger Livewire update
                        e.target.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                }
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    // Handle toast notifications
    function registerToastHandler() {
        if (window.__toastListenerRegistered) {
            return;
        }

        window.__toastListenerRegistered = true;
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
    }

    document.addEventListener('DOMContentLoaded', registerToastHandler);
    document.addEventListener('livewire:navigated', registerToastHandler);
</script>
@endscript