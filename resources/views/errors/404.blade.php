<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - 404</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 50%, #fecaca 100%);
            height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        body.dark {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0a 50%, #2a0505 100%);
        }

        @media (prefers-color-scheme: dark) {
            body:not(.light) {
                background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0a 50%, #2a0505 100%);
            }
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.5;
            animation: float 25s infinite ease-in-out;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.9) 0%, rgba(220, 38, 38, 0.4) 40%, transparent 70%);
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.7) 0%, rgba(185, 28, 28, 0.3) 40%, transparent 70%);
            bottom: -100px;
            right: -100px;
            animation-delay: 5s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.6) 0%, rgba(220, 38, 38, 0.2) 40%, transparent 70%);
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }

        .orb-4 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(185, 28, 28, 0.5) 0%, rgba(153, 27, 27, 0.2) 40%, transparent 70%);
            top: 20%;
            left: 30%;
            animation-delay: 15s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
            25% { transform: translate(80px, -80px) scale(1.15) rotate(90deg); }
            50% { transform: translate(-60px, 60px) scale(0.85) rotate(180deg); }
            75% { transform: translate(60px, 40px) scale(1.1) rotate(270deg); }
        }

        .grid-bg {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(239, 68, 68, 0.03) 1.5px, transparent 1.5px),
                linear-gradient(90deg, rgba(239, 68, 68, 0.03) 1.5px, transparent 1.5px);
            background-size: 80px 80px;
            background-attachment: fixed;
            opacity: 0.4;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(239, 68, 68, 0.6);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.8);
            animation: particleFloat 15s infinite;
        }

        @keyframes particleFloat {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translateY(-100vh) translateX(50px);
                opacity: 0;
            }
        }

        .error-container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 0.75rem;
            max-width: 720px;
            width: 92vw;
            margin: 0 auto;
        }

        .glass-card {
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(30px) saturate(200%);
            border: 2px solid rgba(239, 68, 68, 0.3);
            border-radius: 1.75rem;
            padding: clamp(1.75rem, 3.2vw, 2.5rem) clamp(1.25rem, 3.2vw, 2.75rem);
            box-shadow:
                0 25px 70px -20px rgba(239, 68, 68, 0.25),
                0 0 60px rgba(239, 68, 68, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.9),
                inset 0 -1px 0 rgba(239, 68, 68, 0.1);
            animation: fadeInScale 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(45deg,
                rgba(239, 68, 68, 0.5),
                rgba(220, 38, 38, 0.3),
                rgba(239, 68, 68, 0.5),
                rgba(185, 28, 28, 0.3));
            background-size: 300% 300%;
            border-radius: 3rem;
            z-index: -1;
            animation: gradientRotate 6s ease infinite;
            filter: blur(10px);
        }

        @keyframes gradientRotate {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        body.dark .glass-card {
            background: rgba(17, 24, 39, 0.95);
            border: 2px solid rgba(239, 68, 68, 0.4);
            box-shadow:
                0 25px 70px -20px rgba(0, 0, 0, 0.8),
                0 0 80px rgba(239, 68, 68, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        @media (prefers-color-scheme: dark) {
            body:not(.light) .glass-card {
                background: rgba(17, 24, 39, 0.95);
                border: 2px solid rgba(239, 68, 68, 0.4);
                box-shadow:
                    0 25px 70px -20px rgba(0, 0, 0, 0.8),
                    0 0 80px rgba(239, 68, 68, 0.25),
                    inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .icon-container {
            position: relative;
            display: inline-block;
            margin-bottom: clamp(0.75rem, 2vw, 1.25rem);
        }

        .icon-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid rgba(239, 68, 68, 0.2);
            animation: ringPulse 3s ease-in-out infinite;
        }

        .icon-ring:nth-child(1) {
            width: 120px;
            height: 120px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 0s;
        }

        .icon-ring:nth-child(2) {
            width: 140px;
            height: 140px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 1s;
        }

        .icon-ring:nth-child(3) {
            width: 160px;
            height: 160px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 2s;
        }

        @keyframes ringPulse {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; }
            50% { opacity: 0.5; }
            100% { transform: translate(-50%, -50%) scale(1.2); opacity: 0; }
        }

        .error-icon-bg {
            width: clamp(82px, 11vw, 110px);
            height: clamp(82px, 11vw, 110px);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.15) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: iconPulse 3s ease-in-out infinite;
            box-shadow:
                0 15px 50px rgba(239, 68, 68, 0.3),
                inset 0 0 30px rgba(239, 68, 68, 0.15);
            position: relative;
            z-index: 1;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 15px 50px rgba(239, 68, 68, 0.3); }
            50% { transform: scale(1.08); box-shadow: 0 20px 60px rgba(239, 68, 68, 0.45); }
        }

        .error-icon {
            width: clamp(48px, 7vw, 70px);
            height: clamp(48px, 7vw, 70px);
            color: #ef4444;
            filter: drop-shadow(0 4px 20px rgba(239, 68, 68, 0.5));
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(5deg); }
        }

        .error-code {
            font-size: clamp(3.25rem, 8.5vw, 5.25rem);
            font-weight: 900;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 40%, #991b1b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0.75rem 0 0.25rem;
            line-height: 1;
            letter-spacing: -0.05em;
            filter: drop-shadow(0 0 40px rgba(239, 68, 68, 0.5));
            animation: glowPulse 2s ease-in-out infinite;
            position: relative;
        }

        @keyframes glowPulse {
            0%, 100% { filter: drop-shadow(0 0 40px rgba(239, 68, 68, 0.5)); }
            50% { filter: drop-shadow(0 0 60px rgba(239, 68, 68, 0.8)); }
        }

        .error-heading {
            font-size: clamp(1.25rem, 3.8vw, 1.85rem);
            font-weight: 800;
            color: #111827;
            margin: 0.5rem 0 1rem;
            text-shadow: 0 2px 15px rgba(239, 68, 68, 0.2);
            position: relative;
            display: inline-block;
        }

        .error-heading::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, transparent, #ef4444, transparent);
            border-radius: 2px;
            animation: underlineGlow 2s ease-in-out infinite;
        }

        @keyframes underlineGlow {
            0%, 100% { box-shadow: 0 0 10px rgba(239, 68, 68, 0.4); width: 60px; }
            50% { box-shadow: 0 0 20px rgba(239, 68, 68, 0.8); width: 80px; }
        }

        body.dark .error-heading { color: #f9fafb; text-shadow: 0 2px 20px rgba(239, 68, 68, 0.4); }

        @media (prefers-color-scheme: dark) {
            body:not(.light) .error-heading { color: #f9fafb; text-shadow: 0 2px 20px rgba(239, 68, 68, 0.4); }
        }

        .error-description {
            font-size: clamp(0.9rem, 2.4vw, 1.05rem);
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.8;
            max-width: 520px;
            margin-left: auto;
            margin-right: auto;
        }

        body.dark .error-description { color: #9ca3af; }

        @media (prefers-color-scheme: dark) {
            body:not(.light) .error-description { color: #9ca3af; }
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            direction: rtl;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 9999px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before { width: 300px; height: 300px; }

        .btn-primary {
            color: white;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow:
                0 6px 25px rgba(239, 68, 68, 0.4),
                0 0 40px rgba(239, 68, 68, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow:
                0 12px 35px rgba(239, 68, 68, 0.5),
                0 0 50px rgba(239, 68, 68, 0.35);
        }

        .btn-primary:active { transform: translateY(-2px) scale(0.98); }

        .btn-secondary {
            color: #374151;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(239, 68, 68, 0.25);
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.15);
        }

        body.dark .btn-secondary {
            color: #f9fafb;
            background: rgba(31, 41, 55, 0.9);
            border: 2px solid rgba(239, 68, 68, 0.4);
        }

        @media (prefers-color-scheme: dark) {
            body:not(.light) .btn-secondary {
                color: #f9fafb;
                background: rgba(31, 41, 55, 0.9);
                border: 2px solid rgba(239, 68, 68, 0.4);
            }
        }

        .btn-secondary:hover {
            transform: translateY(-4px) scale(1.02);
            border-color: rgba(239, 68, 68, 0.6);
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3);
        }

        .btn-icon {
            width: 18px;
            height: 18px;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            .orb-1, .orb-2, .orb-3, .orb-4 { opacity: 0.3; }
            .error-code { font-size: 5rem; }
            .error-heading { font-size: 1.5rem; }
            .error-description { font-size: 0.95rem; margin-bottom: 1.5rem; }
            .glass-card { padding: 2.5rem 1.5rem; border-radius: 1.5rem; }
            .error-icon-bg { width: 100px; height: 100px; }
            .error-icon { width: 60px; height: 60px; }
            .icon-ring:nth-child(1) { width: 120px; height: 120px; }
            .icon-ring:nth-child(2) { width: 140px; height: 140px; }
            .icon-ring:nth-child(3) { width: 160px; height: 160px; }
            .action-buttons { flex-direction: column; gap: 0.75rem; }
            .btn { width: 100%; justify-content: center; padding: 1rem 1.5rem; font-size: 0.95rem; }
        }
    </style>
</head>
<body class="light">
@php
    $notFoundTitle = 'الصفحة غير موجودة';
    $notFoundMessage = 'عذراً، لم نتمكن من العثور على الصفحة التي تبحث عنها. ربما تم نقلها أو حذفها أو أن الرابط غير صحيح.';

    try {
        /** @var \App\Settings\TrainingSettings $settings */
        $settings = app(\App\Settings\TrainingSettings::class);
        $notFoundTitle = $settings->not_found_title ?: $notFoundTitle;
        $notFoundMessage = $settings->not_found_message ?: $notFoundMessage;
    } catch (\Throwable $e) {
        // Fallback to defaults (e.g. during early boot / misconfigured DB)
    }

    $loginUrl = url('/home/login');
    $homeUrl = url('/');
@endphp

<script>
    (function() {
        // Detect system theme preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.classList.add('dark');
            document.body.classList.remove('light');
        } else {
            document.body.classList.add('light');
            document.body.classList.remove('dark');
        }

        // Listen for theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            document.body.classList.toggle('dark', e.matches);
            document.body.classList.toggle('light', !e.matches);
        });

        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 15 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            document.body.appendChild(particle);
        }
    })();
</script>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="orb orb-4"></div>
<div class="grid-bg"></div>

<div class="error-container">
    <div class="glass-card">
        <div class="icon-container">
            <div class="icon-ring"></div>
            <div class="icon-ring"></div>
            <div class="icon-ring"></div>
            <div class="error-icon-bg">
                <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <h1 class="error-code">404</h1>
        <h2 class="error-heading">{{ $notFoundTitle }}</h2>
        <p class="error-description">{{ $notFoundMessage }}</p>

        <div class="action-buttons">
            <button onclick="history.back()" class="btn btn-secondary" type="button">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                رجوع
            </button>
        </div>
    </div>
</div>
</body>
</html>
