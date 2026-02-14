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
                <p class="eyebrow">مرحباً بك في نظام التدريب في جمعية الهلال الأحمر الفلسطيني</p>
                <h1>طلب تدريب</h1>
                <p class="lead">
                    يرجى تعبئة البيانات بدقة لاختيار التخصص والقسم المطلوب بناءً على الأماكن التدريبية المتاحة.
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
                @include('livewire.trainee.components.fieldset-training-type')

                <!-- FIELDSET 2: Personal Details (shown conditionally) -->
                @include('livewire.trainee.components.fieldset-personal-details')

                <!-- FIELDSET 3: Training Details (shown conditionally) -->
                @include('livewire.trainee.components.fieldset-training-details')

                <!-- Terms & Conditions Section (shown only when personal details are visible) -->
                @include('livewire.trainee.components.form-terms')

                <!-- Submit Button - Posts to ApplicationFormController::store -->
                @include('livewire.trainee.components.form-footer')
            </form>

            <!-- Toast container -->
            <div id="toast" class="toast">
                <span id="toastMessage"></span>
            </div>
        </div>
    </main>

    <!-- Modern Footer (Hero-style) -->
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-contact">
                <a href="https://wa.me/+970599065032" target="_blank" rel="noopener noreferrer" class="footer-whatsapp">
                    <svg class="whatsapp-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span>تواصل معنا</span>
                </a>
            </div>
            <div class="footer-address">
                <svg class="location-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
                <span>خانيونس - حي الأمل - جمعيةالهلال الأحمر الفلسطيني - <a href="https://adc.edu.ps/" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline; cursor: pointer;">كلية تنمية القدرات الجامعية</a></span>
            </div>
        </div>
    </footer>

    <link rel="stylesheet" href="{{ asset('css/trainee-form.css') }}">
</div>

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
