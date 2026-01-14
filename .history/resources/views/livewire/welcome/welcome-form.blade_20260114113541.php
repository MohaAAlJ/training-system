<div class="app-container">
    <div class="theme-switch">
        <button id="themeToggle" type="button" title="تبديل الوضع">
            <span class="mode-icon">🌓</span>
        </button>
    </div>

    <main class="page welcome-page">
        <header class="hero hero--banner welcome-hero">
            <div class="hero__text">
                <p class="eyebrow">بوابة التدريب</p>
                <h1>مرحباً بك في نظام التدريب</h1>
                <p class="lead">
                    نرحب بك في نظام التدريب. يمكنك من خلال هذه البوابة تقديم طلب التدريب الخاص بك بكل سهولة ويسر.
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

        <div class="card-wrapper">
            <div class="card welcome-card">
                <div class="welcome-content">
                    <h2>ابدأ رحلتك التدريبية</h2>

                    @if (session('error'))
                        <div class="disabled-message" style="margin-bottom: 1rem;">
                            <p class="error-text">{{ session('error') }}</p>
                        </div>
                    @endif

                    @if ($isFormEnabled)
                    <p>للتقديم على برنامج التدريب يرجى إدخال رقم الهوية واختيار نوع التدريب.</p>

                    <div class="welcome-form-fields"
                        style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1rem; max-width: 400px; margin-left: auto; margin-right: auto;">
                        <div class="form-group">
                            <label for="national_id"
                                style="display: block; margin-bottom: 0.5rem; text-align: right; font-weight: 600;">رقم
                                الهوية</label>
                            <input type="text" id="national_id" wire:model="nationalId" class="form-control"
                                placeholder="أدخل رقم الهوية (9 أرقام)" maxlength="9"
                                style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; text-align: center;">
                            @error('nationalId') <span
                                style="color: #d32f2f; font-size: 0.875rem; display: block; margin-top: 0.25rem; text-align: right;">{{ $message }}</span>
                            @error
                        </div>

                        <div class="form-group">
                            <label for="training_type"
                                style="display: block; margin-bottom: 0.5rem; text-align: right; font-weight: 600;">نوع
                                التدريب</label>
                            <select id="training_type" wire:model="trainingType" class="form-control"
                                style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; text-align: center;">
                                <option value="">اختر نوع التدريب</option>
                                @foreach($trainingTypes as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                            @error('trainingType') <span
                                style="color: #d32f2f; font-size: 0.875rem; display: block; margin-top: 0.25rem; text-align: right;">{{ $message }}</span>
                            @error
                                    </div>

                                    <button wire:click="checkApplication" class="glow-button welcome-button"
                                        style="width: 100%; margin-top: 1rem;">
                                        ابدأ تعبئة الطلب
                                    </button>
                                </div>
                            @else
                    <div class="disabled-message">
                        <p class="error-text">عذراً، تقديم الطلبات عبر البوابة مغلق حالياً.</p>
                        <p>نعتذر عن عدم إمكانية استقبال طلبات جديدة في الوقت الحالي. يرجى المحاولة لاحقاً أو التواصل مع
                            الإدارة.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>

@script
<script>
    // Initialize theme on page load
    document.addEventListener('livewire:initialized', () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        const heroElement = document.querySelector('.welcome-hero');
        const welcomeCard = document.querySelector('.welcome-card');

        if (heroElement) {
            heroElement.style.opacity = '0';
            heroElement.style.transform = 'translateY(-20px)';

            setTimeout(() => {
                heroElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                heroElement.style.opacity = '1';
                heroElement.style.transform = 'translateY(0)';
            }, 10);
        }

        if (welcomeCard) {
            welcomeCard.style.opacity = '0';
            welcomeCard.style.transform = 'translateY(20px)';

            setTimeout(() => {
                welcomeCard.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                welcomeCard.style.opacity = '1';
                welcomeCard.style.transform = 'translateY(0)';
            }, 100);
        }

        // Handle theme toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
        }
    });
</script>
@endscript