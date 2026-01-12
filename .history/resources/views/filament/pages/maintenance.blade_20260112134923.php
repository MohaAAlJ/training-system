<x-filament-panels::page>
    <div class="maintenance-page-wrapper">
        <div class="maintenance-container">
            <div class="maintenance-content">
                <div class="icon-wrapper">
                    🔧
                </div>
                <h1 class="maintenance-title">الموقع تحت الصيانة</h1>
                <p class="maintenance-message">{{ $this->maintenanceMessage }}</p>

                <x-filament::button tag="a" :href="route('filament.Home.auth.logout')" color="primary" size="lg"
                    class="mt-8">
                    تسجيل الخروج
                </x-filament::button>

                <div class="footer">
                    شكراً لتفهمكم. سنعود قريباً<br>
                    <span class="system-name">نظام التدريب</span>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .fi-simple-layout {
                background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
            }

            .maintenance-page-wrapper {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .maintenance-container {
                background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                padding: 3px;
                border-radius: 20px;
                max-width: 550px;
                width: 100%;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideIn 0.6s ease-out;
            }

            .maintenance-content {
                background: white;
                border-radius: 17px;
                padding: 50px 40px;
                text-align: center;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-30px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .icon-wrapper {
                width: 90px;
                height: 90px;
                margin: 0 auto 25px;
                background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 45px;
                animation: pulse 2s ease-in-out infinite;
            }

            @keyframes pulse {

                0%,
                100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(30, 60, 114, 0.4);
                }

                50% {
                    transform: scale(1.05);
                    box-shadow: 0 0 0 10px rgba(30, 60, 114, 0);
                }
            }

            .maintenance-title {
                color: #1a202c;
                font-size: 1.9rem;
                font-weight: 700;
                margin-bottom: 20px;
            }

            .maintenance-message {
                color: #4a5568;
                font-size: 1.15rem;
                line-height: 1.8;
                margin-bottom: 10px;
            }

            .footer {
                margin-top: 35px;
                padding-top: 25px;
                border-top: 2px solid #e2e8f0;
                color: #718096;
                font-size: 0.95rem;
            }

            .system-name {
                color: #2a5298;
                font-weight: 600;
            }
        </style>
    @endpush
</x-filament-panels::page>