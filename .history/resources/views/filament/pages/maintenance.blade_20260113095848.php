<x-filament-panels::page class="filament-maintenance-page">
    <div class="maintenance-page-wrapper">
        <div class="maintenance-container">
            <div class="maintenance-content">
                <!-- Icon Section -->
                <div class="icon-wrapper">
                    <svg class="maintenance-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="maintenance-title">الموقع تحت الصيانة</h1>
                
                <!-- Message -->
                <p class="maintenance-message">{{ $this->maintenanceMessage }}</p>

                <!-- Status Badge -->
                <div class="status-badge">
                    <span class="status-dot"></span>
                    <span>جارِ تحديث النظام</span>
                </div>

                <!-- Logout Button -->
                <div class="actions">
                    <form action="{{ route('filament.Home.auth.logout') }}" method="post">
                        @csrf
                        <x-filament::button type="submit" color="gray" size="lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px; margin-left: 8px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                            </svg>
                            تسجيل الخروج
                        </x-filament::button>
                    </form>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <p class="footer-text">نعمل جاهدين لتحسين تجربتكم</p>
                    <div class="system-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                        <span class="system-name">نظام التدريب</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Background Animation -->
        <div class="bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Reset Filament Styles */
            .fi-simple-header,
            .fi-simple-footer {
                display: none !important;
            }

            .fi-simple-main {
                margin: 0 !important;
                padding: 0 !important;
            }

            .fi-simple-layout > div {
                max-width: 100% !important;
                padding: 0 !important;
            }

            .fi-simple-layout {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                min-height: 100vh;
                margin: 0;
                overflow: hidden;
            }

            /* Main Wrapper */
            .maintenance-page-wrapper {
                min-height: 100vh;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                padding: 20px;
            }

            /* Container */
            .maintenance-container {
                width: 100%;
                max-width: 600px;
                position: relative;
                z-index: 10;
                animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            }

            /* Content Card */
            .maintenance-content {
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                border-radius: 32px;
                padding: 50px 40px;
                text-align: center;
                box-shadow: 
                    0 20px 60px rgba(0, 0, 0, 0.3),
                    0 0 0 1px rgba(255, 255, 255, 0.5) inset;
                display: flex;
                flex-direction: column;
                align-items: center;
                position: relative;
                overflow: hidden;
            }

            .maintenance-content::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
                background-size: 200% 100%;
                animation: shimmer 3s linear infinite;
            }

            /* Icon Section */
            .icon-wrapper {
                width: 100px;
                height: 100px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 30px;
                box-shadow: 
                    0 10px 30px rgba(102, 126, 234, 0.4),
                    0 0 0 8px rgba(102, 126, 234, 0.1);
                animation: float 3s ease-in-out infinite;
                transform: rotate(-5deg);
            }

            .maintenance-icon {
                width: 50px;
                height: 50px;
                color: white;
                animation: spin 8s linear infinite;
            }

            /* Title */
            .maintenance-title {
                color: #1a202c;
                font-size: 2.25rem;
                font-weight: 800;
                margin-bottom: 16px;
                letter-spacing: -0.5px;
                line-height: 1.2;
            }

            /* Message */
            .maintenance-message {
                color: #4a5568;
                font-size: 1.125rem;
                line-height: 1.75;
                margin-bottom: 32px;
                max-width: 90%;
            }

            /* Status Badge */
            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #fef3c7;
                color: #92400e;
                padding: 10px 20px;
                border-radius: 50px;
                font-size: 0.875rem;
                font-weight: 600;
                margin-bottom: 36px;
                border: 1px solid #fde68a;
            }

            .status-dot {
                width: 8px;
                height: 8px;
                background: #f59e0b;
                border-radius: 50%;
                animation: pulse 2s ease-in-out infinite;
            }

            /* Actions */
            .actions {
                margin-bottom: 36px;
            }

            /* Footer */
            .footer {
                width: 100%;
                padding-top: 24px;
                border-top: 2px solid #e5e7eb;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }

            .footer-text {
                color: #6b7280;
                font-size: 0.875rem;
                margin: 0;
            }

            .system-info {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #667eea;
            }

            .system-name {
                font-weight: 700;
                font-size: 1rem;
            }

            /* Background Shapes */
            .bg-shapes {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                z-index: 1;
            }

            .shape {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
            }

            .shape-1 {
                width: 300px;
                height: 300px;
                top: -100px;
                right: -100px;
                animation: float 8s ease-in-out infinite;
            }

            .shape-2 {
                width: 200px;
                height: 200px;
                bottom: -50px;
                left: -50px;
                animation: float 6s ease-in-out infinite reverse;
            }

            .shape-3 {
                width: 150px;
                height: 150px;
                top: 50%;
                left: 10%;
                animation: float 10s ease-in-out infinite;
            }

            /* Animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(40px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes float {
                0%, 100% {
                    transform: translateY(0) rotate(-5deg);
                }
                50% {
                    transform: translateY(-20px) rotate(-5deg);
                }
            }

            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }

            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                    transform: scale(1);
                }
                50% {
                    opacity: 0.5;
                    transform: scale(1.2);
                }
            }

            @keyframes shimmer {
                0% {
                    background-position: -200% 0;
                }
                100% {
                    background-position: 200% 0;
                }
            }

            /* Responsive */
            @media (max-width: 640px) {
                .maintenance-content {
                    padding: 40px 24px;
                    border-radius: 24px;
                }

                .maintenance-title {
                    font-size: 1.75rem;
                }

                .maintenance-message {
                    font-size: 1rem;
                    max-width: 100%;
                }

                .icon-wrapper {
                    width: 80px;
                    height: 80px;
                }

                .maintenance-icon {
                    width: 40px;
                    height: 40px;
                }

                .shape {
                    display: none;
                }
            }
        </style>
    @endpush
</x-filament-panels::page>