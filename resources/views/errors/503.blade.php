<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - 503</title>
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
            animation: none;
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
            animation: none;
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
            animation: none;
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
            animation: none;
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
            animation: none;
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

        body.dark .error-heading { color: #f9fafb; text-shadow: 0 2px 20px rgba(239, 68, 68, 0.4); }

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

        .btn-icon {
            width: 18px;
            height: 18px;
            position: relative;
            z-index: 1;
        }

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

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            .orb-1, .orb-2, .orb-3, .orb-4 { opacity: 0.3; }
            .error-code { font-size: 5rem; }
            .error-heading { font-size: 1.5rem; }
            .error-description { font-size: 0.95rem; margin-bottom: 1.5rem; }
            .glass-card { padding: 2.5rem 1.5rem; border-radius: 1.5rem; }
            .action-buttons { flex-direction: column; gap: 0.75rem; }
            .btn { width: 100%; justify-content: center; padding: 1rem 1.5rem; font-size: 0.95rem; }
        }
    </style>
</head>
<body>
@php
    $title = 'الموقع تحت الصيانة';
    $message = 'الموقع تحت الصيانة حالياً. سنعود قريباً.';

    try {
        /** @var \App\Settings\TrainingSettings $settings */
        $settings = app(\App\Settings\TrainingSettings::class);
        $message = $settings->maintenance_message ?: $message;
    } catch (\Throwable $e) {
        // Fallback to defaults
    }

    $homeUrl = url('/');
@endphp

<script>
    (function() {
        const media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

        const applyTheme = () => {
            const isDark = !!media && media.matches;
            document.body.classList.toggle('dark', isDark);
            document.body.classList.toggle('light', !isDark);
        };

        applyTheme();
        media?.addEventListener?.('change', applyTheme);
    })();
</script>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="orb orb-4"></div>
<div class="grid-bg"></div>

<div class="error-container">
    <div class="glass-card">
        <h1 class="error-code">503</h1>
        <h2 class="error-heading">{{ $title }}</h2>
        <p class="error-description">{{ $message }}</p>

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
