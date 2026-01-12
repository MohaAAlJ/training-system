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
                    <p>للتقديم على برنامج التدريب يرجى الضغط على الزر أدناه لتعبئة نموذج الطلب.</p>
                    <a href="{{ route('training.form') }}" class="glow-button welcome-button">
                        اضغط هنا لتعبئة طلبك
                    </a>
                @else
                    <div class="disabled-message">
                        <p class="error-text">عذراً، تقديم الطلبات عبر البوابة مغلق حالياً.</p>
                        <p>نعتذر عن عدم إمكانية استقبال طلبات جديدة في الوقت الحالي. يرجى المحاولة لاحقاً أو التواصل مع الإدارة.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </main>
</div>

@script
<script>
    // Animate hero and card for a subtle entrance
    document.addEventListener('livewire:initialized', () => {
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
    });
</script>
@endscript
