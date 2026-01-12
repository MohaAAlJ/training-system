<x-filament-panels::page class="filament-maintenance-page">
    <div class="maintenance-page-wrapper">
        <div class="maintenance-container">
            <div class="maintenance-content">
                <div class="icon-wrapper">
                    <span class="animate-pulse">🔧</span>
                </div>
                <h1 class="maintenance-title">الموقع تحت الصيانة</h1>
                <p class="maintenance-message">{{ $this->maintenanceMessage }}</p>

                <div class="actions">
                    <x-filament::button tag="a" :href="route('filament.Home.auth.logout')" color="danger" size="xl"
                        outlined>
                        تسجيل الخروج
                    </x-filament::button>
                </div>

                <div class="footer">
                    <p>نعمل جاهدين لتحسين تجربتكم</p>
                    <span class="system-name">نظام التدريب</span>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Hide Default Filament Elements */
            .fi-simple-header,
            .fi-simple-footer {
                display: none !important;
            }

            .fi-simple-main {
                margin: 0 !important;
                padding: 0 !important;
            }

            .fi-simple-layout>div {
                max-width: 100% !important;
                padding: 0 !important;
            }

            /* Full Screen Background */
            .fi-simple-layout {
                background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
                min-height: 100vh;
                margin: 0;
                overflow: hidden;
            }

            .maintenance-page-wrapper {
                min-height: 100vh;
                width: 100vw;
                display: flex;
                align-items: center;
                justify-content: center;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 9999;
                /* Optional Pattern Overlay */
                background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
                background-size: 30px 30px;
            }

            .maintenance-container {
                width: 100%;
                max-width: 700px;
                padding: 20px;
                animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .maintenance-content {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 24px;
                padding: 60px 40px;
                text-align: center;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                border: 1px solid rgba(255, 255, 255, 0.5);
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .icon-wrapper {
                width: 120px;
                height: 120px;
                background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 50px;
                margin-bottom: 30px;
                box-shadow: inset 0 2px 4px rgba(255, 255, 255, 1), 0 10px 20px rgba(0, 0, 0, 0.1);
            }

            .maintenance-title {
                color: #1a202c;
                font-size: 2.5rem;
                font-weight: 800;
                margin-bottom: 15px;
                letter-spacing: -0.5px;
            }

            .maintenance-message {
                color: #4a5568;
                font-size: 1.25rem;
                line-height: 1.6;
                margin-bottom: 40px;
                max-width: 80%;
            }

            .actions {
                margin-bottom: 40px;
            }

            .footer {
                width: 100%;
                padding-top: 20px;
                border-top: 1px solid #edf2f7;
                color: #718096;
                font-size: 0.95rem;
            }

            .system-name {
                display: block;
                margin-top: 5px;
                color: #2a5298;
                font-weight: 700;
                font-size: 1.1rem;
            }

            @keyframes scaleIn {
                from {
                    opacity: 0;
                    transform: scale(0.9) translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            /* Responsive Adjustments */
            @media (max-width: 640px) {
                .maintenance-content {
                    padding: 40px 20px;
                }

                .maintenance-title {
                    font-size: 1.8rem;
                }

                .maintenance-message {
                    font-size: 1.1rem;
                    max-width: 100%;
                }
            }
        </style>
    @endpush
</x-filament-panels::page>