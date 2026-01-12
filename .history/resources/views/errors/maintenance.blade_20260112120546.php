<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الموقع تحت الصيانة</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Block all interactions with the page */
        html::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 999998;
            pointer-events: all;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999999;
            pointer-events: none;
        }

        .maintenance-container {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 3px;
            border-radius: 20px;
            max-width: 550px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.6s ease-out;
            pointer-events: all;
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
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(30, 60, 114, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(30, 60, 114, 0);
            }
        }

        h1 {
            color: #1a202c;
            font-size: 1.9rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        p {
            color: #4a5568;
            font-size: 1.15rem;
            line-height: 1.8;
            margin-bottom: 10px;
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
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-content">
            <div class="icon-wrapper">
                🔧
            </div>
            <h1>الموقع تحت الصيانة</h1>
            <p>{{ $message }}</p>
            
            <form method="POST" action="{{ route('filament.Home.auth.logout') }}" target="_top" style="margin: 0;">
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