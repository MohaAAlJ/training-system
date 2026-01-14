<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>صيانة الموقع</title>
    <style>
        :root {
            --bg-color: #f4f6f9;
            --container-bg: #ffffff;
            --text-color: #2d3748;
            --accent-color: #d9534f;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        body,
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        .maintenance-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: var(--bg-color);
            z-index: 100000;
            /* Ensure it stays on top of everything */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            background: var(--container-bg);
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
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

        .icon {
            font-size: 64px;
            margin-bottom: 24px;
            display: block;
        }

        h1 {
            color: var(--accent-color);
            margin: 0 0 16px 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        p {
            color: var(--text-color);
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 0;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9rem;
            color: #a0aec0;
        }
    </style>
</head>

<body>
    <div class="maintenance-wrapper">
        <div class="container">
            <span class="icon">🔧</span>
            <h1>وضع الصيانة</h1>
            <p>{{ $message }}</p>
            <div class="footer">
                شكراً لتفهمكم. سنعود قريباً.
            </div>
        </div>
    </div>
</body>

</html>