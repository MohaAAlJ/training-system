<div class="app-container">
    <div class="theme-switch">
        <button id="themeToggle" type="button" title="تبديل الوضع">
            <span class="mode-icon">🌓</span>
        </button>
    </div>

    <main class="page welcome-page">
        <header class="hero hero--banner welcome-hero">
            <div class="hero__text">
                <p class="eyebrow">مرحباً بك في نظام التدريب في جمعية الهلال الأحمر الفلسطيني</p>
                <h1>بوابة التدريب</h1>
                <p class="lead">
                    يمكنكم من خلال هذه البوابة  تقديم طلبات التدريب وفق الإجراءات المعتمدة.
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
                        <div class="welcome-inputs" style="margin-top: 1.5rem; text-align: center;">
                            <button wire:click="startApplication" class="glow-button welcome-button"
                                style="width: 100%; border: none; cursor: pointer;">
                                ابدأ تعبئة طلبك
                            </button>
                        </div>
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
