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
                <h1>مرحباً بك في نظام التدريب</h1>
                <p class="lead">
                    بوابة التسجيل الموحدة لجميع برامج التدريب
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

        <!-- Welcome Card -->
        <div class="card-wrapper">
            <div class="card">
                <div class="welcome-content">
                    <h2>خطوات بسيطة للبدء</h2>
                    
                    <div class="steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h3>اختر نوع البرنامج</h3>
                            <p>حدد ما إذا كنت تريد برنامج تدريب جامعي أو عملي</p>
                        </div>

                        <div class="step">
                            <div class="step-number">2</div>
                            <h3>أدخل بياناتك</h3>
                            <p>قم بملء نموذج التسجيل ببيانات صحيحة وكاملة</p>
                        </div>

                        <div class="step">
                            <div class="step-number">3</div>
                            <h3>أرسل طلبك</h3>
                            <p>اضغط على زر الإرسال لتقديم طلبك للقبول</p>
                        </div>

                        <div class="step">
                            <div class="step-number">4</div>
                            <h3>انتظر التأكيد</h3>
                            <p>ستتلقى تأكيداً عبر البريد الإلكتروني أو الهاتف</p>
                        </div>
                    </div>

                    <div class="cta-section">
                        <p class="highlight">هل أنت مستعد للتقديم؟</p>
                        <a href="{{ route('training.form') }}" class="btn btn-primary btn-lg">
                            ابدأ الآن
                        </a>
                    </div>

                    <div class="info-box">
                        <h3>معلومات مهمة</h3>
                        <ul>
                            <li>تأكد من صحة جميع البيانات قبل الإرسال</li>
                            <li>لا يمكن تعديل الهوية الوطنية بعد التقديم</li>
                    <li>الملفات المقبولة: JPG, PNG, PDF (بحد أقصى 2MB)</li>
                    <li>للاستفسارات تواصل معنا عبر البريد: info@training.gov</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@script
<script>
    // Animate page elements on load
    document.addEventListener('livewire:initialized', () => {
        const heroElement = document.querySelector('.hero');
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
