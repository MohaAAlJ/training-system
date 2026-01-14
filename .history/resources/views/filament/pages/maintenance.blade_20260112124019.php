<x-filament-panels::page>
    <div class="maintenance-page-wrapper">
        <div class="maintenance-container">
            <div class="maintenance-content">
                <div class="icon-wrapper">
                    🔧
                </div>
                <h1 class="maintenance-title">الموقع تحت الصيانة</h1>
                <p class="maintenance-message">{{ $this->maintenanceMessage }}</p>

                <form method="POST" action="{{ route('filament.Home.auth.logout') }}" target="_top" class="logout-form">
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
    </div>

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

        .logout-form {
            margin: 0;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.4);
        }

        .logout-btn:active {
            transform: translateY(0);
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
</x-filament-panels::page>