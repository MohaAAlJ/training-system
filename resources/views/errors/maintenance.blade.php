<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الموقع تحت الصيانة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Block interactions with the app behind */
        html::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 999998;
            pointer-events: all;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(6px);
        }

        @media (prefers-color-scheme: dark) {
            html::before {
                background: rgba(0, 0, 0, 0.6);
            }
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: fixed;
            inset: 0;
            z-index: 999999;
            pointer-events: none;

            display: flex;
            align-items: center;
            justify-content: center;

            /* Neon background */
            background:
                radial-gradient(800px 500px at 15% 10%, rgba(239, 68, 68, 0.35), transparent 60%),
                radial-gradient(700px 480px at 85% 80%, rgba(220, 38, 38, 0.28), transparent 60%),
                linear-gradient(135deg, #fef2f2 0%, #fee2e2 50%, #fecaca 100%);
        }

        @media (prefers-color-scheme: dark) {
            body {
                background:
                    radial-gradient(900px 520px at 15% 10%, rgba(239, 68, 68, 0.22), transparent 60%),
                    radial-gradient(800px 520px at 85% 80%, rgba(220, 38, 38, 0.18), transparent 60%),
                    linear-gradient(135deg, #0a0a0a 0%, #1a0a0a 50%, #2a0505 100%);
            }
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0.45;
            background-image:
                linear-gradient(rgba(239, 68, 68, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(239, 68, 68, 0.06) 1px, transparent 1px);
            background-size: 80px 80px;
        }

        .maintenance-container {
            pointer-events: all;
            width: min(92vw, 680px);
            padding: 2px;
            border-radius: 22px;
            background: linear-gradient(45deg,
                rgba(239, 68, 68, 0.6),
                rgba(220, 38, 38, 0.35),
                rgba(239, 68, 68, 0.6),
                rgba(185, 28, 28, 0.35));
            background-size: 300% 300%;
            animation: borderGlow 8s ease infinite;
            box-shadow:
                0 30px 80px rgba(239, 68, 68, 0.22),
                0 20px 60px rgba(0, 0, 0, 0.25);
        }

        @keyframes borderGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .maintenance-content {
            border-radius: 20px;
            padding: clamp(1.5rem, 3.2vw, 2.25rem) clamp(1.25rem, 3.2vw, 2.5rem);
            text-align: center;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(18px) saturate(180%);
            border: 1px solid rgba(239, 68, 68, 0.18);
        }

        @media (prefers-color-scheme: dark) {
            .maintenance-content {
                background: rgba(17, 24, 39, 0.92);
                border: 1px solid rgba(239, 68, 68, 0.25);
            }
        }

        .icon-wrapper {
            width: clamp(58px, 9vw, 76px);
            height: clamp(58px, 9vw, 76px);
            margin: 0 auto clamp(0.75rem, 2vw, 1.25rem);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(28px, 4vw, 38px);
            color: #ef4444;
            background: rgba(239, 68, 68, 0.12);
            box-shadow: 0 10px 35px rgba(239, 68, 68, 0.25);
            animation: pulse 2.4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.04); }
        }

        h1 {
            color: #111827;
            font-size: clamp(1.2rem, 3.6vw, 1.6rem);
            font-weight: 800;
            margin-bottom: 0.75rem;
        }

        @media (prefers-color-scheme: dark) {
            h1 { color: #f9fafb; }
        }

        p {
            color: #6b7280;
            font-size: clamp(0.95rem, 2.4vw, 1.05rem);
            line-height: 1.8;
            margin-bottom: 0.5rem;
        }

        @media (prefers-color-scheme: dark) {
            p { color: #cbd5e1; }
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.25rem;
            padding: 0.9rem 1.75rem;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 9999px;
            font-size: 0.95rem;
            font-weight: 700;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            cursor: pointer;
            box-shadow:
                0 10px 30px rgba(239, 68, 68, 0.3),
                0 0 40px rgba(239, 68, 68, 0.12);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow:
                0 14px 34px rgba(239, 68, 68, 0.38),
                0 0 50px rgba(239, 68, 68, 0.18);
        }

        .logout-btn:active { transform: translateY(0); }

        .footer {
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(239, 68, 68, 0.15);
            color: #6b7280;
            font-size: 0.95rem;
        }

        @media (prefers-color-scheme: dark) {
            .footer { color: #94a3b8; border-top-color: rgba(239, 68, 68, 0.22); }
        }

        .system-name {
            color: #dc2626;
            font-weight: 800;
        }
    </style>
</head>

<body>
    <div class="maintenance-container">
        <div class="maintenance-content">
            <div class="icon-wrapper">
                🔧
            </div>
            <h1>الموقع تحت الصيانة</h1>
            <p>{{ $message }}</p>

            <form method="POST" action="{{ route('filament.home.auth.logout') }}" target="_top" style="margin: 0;">
                @csrf
                <button type="submit" class="logout-btn">
                    تسجيل الخروج
                </button>
            </form>

            <div class="footer">
                شكراً لتفهمكم. سنعود قريباً<br>
                <span class="system-name">نظام التدريب</span>
            </div>
        </div>
    </div>
</body>

</html>