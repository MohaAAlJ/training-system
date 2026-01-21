<x-filament-panels::page class="filament-maintenance-page" x-data="{
    logout() {
        document.getElementById('logout-form').submit();
    },
    init() {
        // Theme initialization
        const media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
        const applyTheme = () => {
            const isDark = !!media && media.matches;
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.classList.toggle('light', !isDark);
        };
        applyTheme();
        media?.addEventListener?.('change', applyTheme);

        // Fixed particle positions (not random) - consistent on every page load
        const particlePositions = [
            { left: 5, delay: 0, duration: 12 },
            { left: 15, delay: 2, duration: 14 },
            { left: 25, delay: 4, duration: 13 },
            { left: 35, delay: 1, duration: 15 },
            { left: 45, delay: 3, duration: 11 },
            { left: 55, delay: 5, duration: 13 },
            { left: 65, delay: 2, duration: 14 },
            { left: 75, delay: 4, duration: 12 },
            { left: 85, delay: 1, duration: 15 },
            { left: 95, delay: 3, duration: 13 },
            { left: 10, delay: 5, duration: 14 },
            { left: 20, delay: 0, duration: 12 },
            { left: 30, delay: 3, duration: 15 },
            { left: 40, delay: 1, duration: 13 },
            { left: 50, delay: 4, duration: 14 },
            { left: 60, delay: 2, duration: 12 },
            { left: 70, delay: 5, duration: 13 },
            { left: 80, delay: 0, duration: 15 },
            { left: 90, delay: 3, duration: 14 },
            { left: 12, delay: 1, duration: 12 }
        ];

        const container = document.querySelector('.bg-layer');
        if (container) {
            particlePositions.forEach(pos => {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = pos.left + '%';
                particle.style.animationDelay = pos.delay + 's';
                particle.style.animationDuration = pos.duration + 's';
                container.appendChild(particle);
            });
        }
    }
}" @init="init()">
    @php
        $maintenanceTitle = 'الموقع تحت الصيانة';
        $maintenanceMessage = 'الموقع تحت الصيانة حالياً. سيعود قريباً.';
        $logoUrl = asset('favicon.ico');

        try {
            /** @var \App\Settings\TrainingSettings $settings */
            $settings = app(\App\Settings\TrainingSettings::class);
            $maintenanceTitle = $settings->maintenance_title ?: $maintenanceTitle;
            $maintenanceMessage = $settings->maintenance_message ?: $maintenanceMessage;
        } catch (\Throwable $e) {
            // Fallback
        }
    @endphp

    <div class="maintenance-page-wrapper">
        <div class="bg-layer">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            <div class="orb orb-4"></div>
            <div class="grid-bg"></div>
        </div>

        <div class="maintenance-container">
            <div class="glass-card">
                <div class="logo-container">
                    <img src="{{ $logoUrl }}" alt="Logo" class="app-logo">
                </div>

                <div class="icon-container">
                    <div class="icon-ring"></div>
                    <div class="icon-ring"></div>
                    <div class="icon-ring"></div>
                    <div class="error-icon-bg">
                        <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                    </div>
                </div>

                <h1 class="maintenance-code">503</h1>
                <h2 class="maintenance-heading">{{ $maintenanceTitle }}</h2>
                <p class="maintenance-description">{{ $maintenanceMessage }}</p>

                <div class="action-buttons">
                    <form id="logout-form" action="{{ route('filament.home.auth.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <button @click="logout()" type="button" class="btn btn-logout">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        تسجيل الخروج
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Reset and standard styles */
            .filament-maintenance-page {
                display: flex;
                width: 100%;
                min-height: 100vh;
                min-height: 100dvh;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 50%, #fecaca 100%);
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                overflow: hidden;
                z-index: 50;
            }

            html.dark .filament-maintenance-page {
                background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0a 50%, #2a0505 100%);
            }

            .orb {
                position: fixed;
                border-radius: 50%;
                filter: blur(100px);
                opacity: 0.5;
                animation: float 25s infinite ease-in-out;
                pointer-events: none;
            }

            .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(239, 68, 68, 0.9) 0%, rgba(220, 38, 38, 0.4) 40%, transparent 70%); top: -150px; left: -150px; animation-delay: 0s; }
            .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(220, 38, 38, 0.7) 0%, rgba(185, 28, 28, 0.3) 40%, transparent 70%); bottom: -100px; right: -100px; animation-delay: 5s; }
            .orb-3 { width: 350px; height: 350px; background: radial-gradient(circle, rgba(239, 68, 68, 0.6) 0%, rgba(220, 38, 38, 0.2) 40%, transparent 70%); top: 50%; right: 10%; animation-delay: 10s; }
            .orb-4 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(185, 28, 28, 0.5) 0%, rgba(153, 27, 27, 0.2) 40%, transparent 70%); top: 20%; left: 30%; animation-delay: 15s; }

            @keyframes float {
                0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
                25% { transform: translate(80px, -80px) scale(1.15) rotate(90deg); }
                50% { transform: translate(-60px, 60px) scale(0.85) rotate(180deg); }
                75% { transform: translate(60px, 40px) scale(1.1) rotate(270deg); }
            }

            .bg-layer {
                position: fixed;
                inset: 0;
                z-index: 5;
                pointer-events: none;
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
                bottom: -10px;
                animation: particleFloat 15s infinite;
            }

            @keyframes particleFloat {
                0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
                10% { opacity: 1; }
                90% { opacity: 1; }
                100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
            }

            .maintenance-page-wrapper {
                position: fixed;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
                padding: 1rem;
                z-index: 10;
            }

            .maintenance-container {
                position: relative;
                z-index: 20;
                width: 92vw;
                max-width: 720px;
                text-align: center;
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
                animation: fadeInScale 0.8s cubic-bezier(0.16, 1, 0.3, 1), cardFloat 6s ease-in-out infinite;
                position: relative;
                overflow: hidden;
            }

            .glass-card::before {
                content: '';
                position: absolute;
                inset: -2px;
                background: linear-gradient(45deg, rgba(239, 68, 68, 0.5), rgba(220, 38, 38, 0.3), rgba(239, 68, 68, 0.5), rgba(185, 28, 28, 0.3));
                background-size: 300% 300%;
                border-radius: 3rem;
                z-index: -1;
                animation: gradientRotate 6s ease infinite;
                filter: blur(10px);
            }

            @keyframes fadeInScale { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
            @keyframes cardFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
            @keyframes gradientRotate { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }

            html.dark .glass-card {
                background: rgba(17, 24, 39, 0.95);
                border: 2px solid rgba(239, 68, 68, 0.4);
                box-shadow: 0 25px 70px -20px rgba(0, 0, 0, 0.8), 0 0 80px rgba(239, 68, 68, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            .logo-container { margin-bottom: 2rem; }
            .app-logo { height: clamp(50px, 8vw, 70px); width: auto; margin: 0 auto; filter: drop-shadow(0 4px 12px rgba(239, 68, 68, 0.3)); }

            .icon-container { position: relative; display: inline-block; margin-bottom: clamp(0.75rem, 2vw, 1.25rem); }
            .icon-ring { position: absolute; border-radius: 50%; border: 2px solid rgba(239, 68, 68, 0.2); animation: ringPulse 3s ease-in-out infinite; left: 50%; top: 50%; transform: translate(-50%, -50%); }
            .icon-ring:nth-child(1) { width: 120px; height: 120px; animation-delay: 0s; }
            .icon-ring:nth-child(2) { width: 140px; height: 140px; animation-delay: 1s; }
            .icon-ring:nth-child(3) { width: 160px; height: 160px; animation-delay: 2s; }

            @keyframes ringPulse { 0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; } 50% { opacity: 0.5; } 100% { transform: translate(-50%, -50%) scale(1.2); opacity: 0; } }

            .error-icon-bg { width: clamp(82px, 11vw, 110px); height: clamp(82px, 11vw, 110px); background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.15) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: iconPulse 3s ease-in-out infinite; box-shadow: 0 15px 50px rgba(239, 68, 68, 0.3), inset 0 0 30px rgba(239, 68, 68, 0.15); position: relative; z-index: 1; }
            @keyframes iconPulse { 0%, 100% { transform: scale(1); box-shadow: 0 15px 50px rgba(239, 68, 68, 0.3); } 50% { transform: scale(1.08); box-shadow: 0 20px 60px rgba(239, 68, 68, 0.45); } }
            .error-icon { width: clamp(48px, 7vw, 70px); height: clamp(48px, 7vw, 70px); color: #ef4444; filter: drop-shadow(0 4px 20px rgba(239, 68, 68, 0.5)); animation: iconFloat 3s ease-in-out infinite; }
            @keyframes iconFloat { 0%, 100% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-12px) rotate(5deg); } }

            .maintenance-code {
                font-size: clamp(3.25rem, 8.5vw, 5.25rem); font-weight: 900; background: linear-gradient(135deg, #ef4444 0%, #dc2626 40%, #991b1b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0.75rem 0 0.25rem; line-height: 1; letter-spacing: -0.05em; filter: drop-shadow(0 0 40px rgba(239, 68, 68, 0.5)); animation: glowPulse 2s ease-in-out infinite; position: relative;
            }
            @keyframes glowPulse { 0%, 100% { filter: drop-shadow(0 0 40px rgba(239, 68, 68, 0.5)); } 50% { filter: drop-shadow(0 0 60px rgba(239, 68, 68, 0.8)); } }

            .maintenance-heading {
                font-size: clamp(1.25rem, 3.8vw, 1.85rem); font-weight: 800; color: #111827; margin: 0.5rem 0 1rem; text-shadow: 0 2px 15px rgba(239, 68, 68, 0.2); position: relative; display: inline-block;
            }
            .maintenance-heading::after { content: ''; position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 60px; height: 4px; background: linear-gradient(90deg, transparent, #ef4444, transparent); border-radius: 2px; animation: underlineGlow 2s ease-in-out infinite; }
            @keyframes underlineGlow { 0%, 100% { box-shadow: 0 0 10px rgba(239, 68, 68, 0.4); width: 60px; } 50% { box-shadow: 0 0 20px rgba(239, 68, 68, 0.8); width: 80px; } }

            html.dark .maintenance-heading { color: #f9fafb; text-shadow: 0 2px 20px rgba(239, 68, 68, 0.4); }

            .maintenance-description { font-size: clamp(0.9rem, 2.4vw, 1.05rem); color: #6b7280; margin-bottom: 2rem; line-height: 1.8; max-width: 520px; margin-left: auto; margin-right: auto; }
            html.dark .maintenance-description { color: #9ca3af; }

            .btn { display: inline-flex; align-items: center; gap: 0.75rem; padding: 0.9rem 1.75rem; font-size: 0.95rem; font-weight: 600; border-radius: 9999px; cursor: pointer; text-decoration: none; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); border: none; position: relative; overflow: hidden; }
            .btn::before { content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; border-radius: 50%; background: rgba(255, 255, 255, 0.3); transform: translate(-50%, -50%); transition: width 0.6s, height 0.6s; }
            .btn:hover::before { width: 300px; height: 300px; }
            .btn-logout { color: white; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 6px 25px rgba(239, 68, 68, 0.4), 0 0 40px rgba(239, 68, 68, 0.2); }
            .btn-logout:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 12px 35px rgba(239, 68, 68, 0.5), 0 0 50px rgba(239, 68, 68, 0.35); }
            .btn-icon { width: 18px; height: 18px; position: relative; z-index: 1; }

            @media (max-width: 768px) {
                .error-code { font-size: 5rem; }
                .maintenance-heading { font-size: 1.5rem; }
                .glass-card { padding: 2.5rem 1.5rem; }
                .btn { width: 100%; justify-content: center; }
            }
        </style>
    @endpush
</x-filament-panels::page>
