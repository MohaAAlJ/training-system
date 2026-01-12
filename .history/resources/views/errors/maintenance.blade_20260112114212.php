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

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999999;
        }

        .maintenance-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.5s ease-out;
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
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        h1 {
            color: #2d3748;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        p {
            color: #4a5568;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
            color: #a0aec0;
            font-size: 0.9rem;
        }

        .system-name {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="icon-wrapper">
            🔧
        </div>
        <h1>الموقع تحت الصيانة</h1>
        <p>{{ $message }}</p>
        <div class="footer">
            شكراً لتفهمكم. سنعود قريباً<br>
            <span class="system-name">نظام التدريب</span>
        </div>
    </div>
</body>
</html>