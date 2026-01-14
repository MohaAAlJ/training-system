<x-filament-panels::page class="filament-maintenance-page">
    <div class="maintenance-page-wrapper">
        <div class="maintenance-container">
            <div class="maintenance-content">
                <!-- Icon Section -->
                <div class="icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="maintenance-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="maintenance-title">الموقع تحت الصيانة</h1>
                
                <!-- Message -->
                <div class="maintenance-message-box">
                    <p class="maintenance-message">{{ $this->maintenanceMessage }}</p>
                </div>

                <!-- Logout Button -->
                <div class="actions">
                    <form action="{{ route('filament.Home.auth.logout') }}" method="post">
                        @csrf
                        <x-filament::button type="submit" color="gray" size="lg" icon="heroicon-o-arrow-left-on-rectangle">
                            تسجيل الخروج
                        </x-filament::button>
                    </form>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <p class="footer-text">نعمل جاهدين لتحسين تجربتكم</p>
                </div>
            </div>
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

            /* Use Filament's background color */
            .fi-simple-layout {
                background: rgb(var(--gray-950)) !important;
                min-height: 100vh;
                margin: 0;
            }

            /* Main Wrapper */
            .maintenance-page-wrapper {
                min-height: 100vh;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            /* Container */
            .maintenance-container {
                width: 100%;
                max-width: 520px;
            }

            /* Content Card - Filament Style */
            .maintenance-content {
                background: rgb(var(--gray-900));
                border: 1px solid rgb(var(--gray-800));
                border-radius: 12px;
                padding: 48px 32px;
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
                box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
                animation: fadeIn 0.6s ease-out;
                position: relative;
                overflow: hidden;
            }

            .maintenance-content::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 2px;
                background: linear-gradient(90deg, transparent, rgb(var(--warning-400)), transparent);
                animation: slide 3s ease-in-out infinite;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes slide {
                0% {
                    left: -100%;
                }
                50%, 100% {
                    left: 100%;
                }
            }

            /* Icon Section - Filament Style */
            .icon-wrapper {
                width: 80px;
                height: 80px;
                background: rgb(var(--gray-800));
                border: 1px solid rgb(var(--gray-700));
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 24px;
                position: relative;
                transition: transform 0.3s ease;
            }

            .icon-wrapper::before {
                content: '';
                position: absolute;
                inset: -1px;
                border-radius: 12px;
                padding: 1px;
                background: linear-gradient(135deg, rgb(var(--warning-400)), rgb(var(--warning-600)));
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .icon-wrapper:hover {
                transform: scale(1.05);
            }

            .icon-wrapper:hover::before {
                opacity: 1;
            }

            .maintenance-icon {
                width: 40px;
                height: 40px;
                color: rgb(var(--warning-400));
                animation: pulse-icon 3s ease-in-out infinite;
            }

            @keyframes pulse-icon {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.7;
                }
            }

            /* Title - Filament Typography */
            .maintenance-title {
                color: rgb(var(--gray-50));
                font-size: 1.875rem;
                font-weight: 700;
                margin-bottom: 16px;
                line-height: 1.2;
                animation: fadeInDown 0.6s ease-out 0.2s both;
            }

            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Message Box */
            .maintenance-message-box {
                width: 100%;
                background: rgb(var(--warning-400) / 0.1);
                border: 1px solid rgb(var(--warning-400) / 0.2);
                border-radius: 8px;
                padding: 16px;
                margin-bottom: 32px;
                position: relative;
                overflow: hidden;
            }

            .maintenance-message-box::before {
                content: '';
                position: absolute;
                right: 0;
                top: 0;
                width: 4px;
                height: 100%;
                background: rgb(var(--warning-400));
                animation: pulse-line 2s ease-in-out infinite;
            }

            @keyframes pulse-line {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.5;
                }
            }

            .maintenance-message {
                color: rgb(var(--gray-300));
                font-size: 1rem;
                line-height: 1.6;
                margin: 0;
            }

            /* Actions */
            .actions {
                margin-bottom: 32px;
            }

            .actions button {
                transition: all 0.2s ease;
            }

            .actions button:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgb(0 0 0 / 0.15);
            }

            .actions button:active {
                transform: translateY(0);
            }

            /* Footer */
            .footer {
                width: 100%;
                padding-top: 24px;
                border-top: 1px solid rgb(var(--gray-800));
                animation: fadeIn 0.6s ease-out 0.4s both;
            }

            .footer-text {
                color: rgb(var(--gray-400));
                font-size: 0.875rem;
                margin: 0;
                position: relative;
                display: inline-block;
            }

            .footer-text::after {
                content: '...';
                animation: dots 1.5s steps(4, end) infinite;
            }

            @keyframes dots {
                0%, 20% {
                    content: '.';
                }
                40% {
                    content: '..';
                }
                60%, 100% {
                    content: '...';
                }
            }

            /* Responsive */
            @media (max-width: 640px) {
                .maintenance-content {
                    padding: 32px 24px;
                }

                .maintenance-title {
                    font-size: 1.5rem;
                }

                .icon-wrapper {
                    width: 64px;
                    height: 64px;
                }

                .maintenance-icon {
                    width: 32px;
                    height: 32px;
                }
            }
        </style>
    @endpush
</x-filament-panels::page>