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
                        <div class="welcome-inputs" style="margin-top: 1.5rem; text-align: right; direction: rtl;">
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label for="trainingType"
                                    style="display: block; margin-bottom: 0.5rem; font-weight: bold;">نوع التدريب *</label>
                                <select wire:model="trainingType" id="trainingType" class="form__input"
                                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #ddd;">
                                    <option value="">-- اختر نوع التدريب --</option>
                                    @foreach ($trainingTypes as $type)
                                        <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('trainingType') <small class="error-text"
                                style="color: #e63946;">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="nationalId"
                                    style="display: block; margin-bottom: 0.5rem; font-weight: bold;">رقم الهوية *</label>
                                <input type="text" wire:model="nationalId" id="nationalId" maxlength="9"
                                    placeholder="رقم الهوية (9 أرقام)" class="form__input"
                                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #ddd;">
                                @error('nationalId') <small class="error-text"
                                style="color: #e63946;">{{ $message }}</small> @enderror
                            </div>

                            <button wire:click="startApplication" class="glow-button welcome-button"
                                style="width: 100%; border: none; cursor: pointer;">
                                ابدأ تعبئة طلبك
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