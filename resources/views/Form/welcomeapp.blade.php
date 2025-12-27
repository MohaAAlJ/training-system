<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>مرحباً بك - نظام التدريب التعاوني</title>
        <link rel="stylesheet" href="{{ asset('form-assets/trainee-app/styles.css') }}" />
        <link rel="stylesheet" href="{{ asset('form-assets/welcome/styles.css') }}" />
    </head>
    <body>
        <main class="page welcome-page">
            <header class="hero hero--banner welcome-hero">
                <div class="hero__text">
                    <p class="eyebrow">بوابة التدريب التعاوني</p>
                    <h1>مرحباً بك في نظام التدريب</h1>
                    <p class="lead">
                        نرحب بك في نظام التدريب التعاوني. يمكنك من خلال هذه البوابة تقديم طلب التدريب الخاص بك بكل سهولة ويسر.
                    </p>
                </div>
                <div class="hero__brand">
                    <div class="hero__logo">
                        <img
                            src="{{ asset('form-assets/trainee-app/logo.png') }}"
                            alt="شعار PRCS"
                        />
                    </div>
                    <div class="hero__brand-meta">
                        <span class="hero__brand-name">PRCS</span>
                        <span class="hero__brand-sub">نظام التدريب التعاوني</span>
                    </div>
                </div>
            </header>

            <div class="card welcome-card">
                <div class="welcome-content">
                    <h2>ابدأ رحلتك التدريبية</h2>
                    @if($isFormEnabled)
                        <p>للتقديم على برنامج التدريب التعاوني، يرجى الضغط على الزر أدناه لتعبئة نموذج الطلب.</p>
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
        </main>

        <script src="{{ asset('form-assets/welcome/app.js') }}" defer></script>
    </body>
</html>
