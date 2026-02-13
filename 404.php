<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <meta http-equiv="refresh" content="10;url=index.php">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0F1117;
            color: #F9FAFB;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 560px;
        }
        .error-code {
            font-size: 140px;
            font-weight: 800;
            background: linear-gradient(135deg, #1E90FF, #6366F1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #F9FAFB;
        }
        p {
            font-size: 16px;
            color: #9CA3AF;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #1E90FF, #6366F1);
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(30, 144, 255, 0.4);
        }
        .redirect-notice {
            margin-top: 24px;
            font-size: 13px;
            color: #6B7280;
        }
        .redirect-notice span {
            color: #1E90FF;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you're looking for doesn't exist or has been moved. Let's get you back on track.</p>
        <a href="index.php" class="btn-home">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Go to Homepage
        </a>
        <p class="redirect-notice">You will be redirected to the homepage in <span id="countdown">10</span> seconds.</p>
    </div>
    <script>
        var seconds = 10;
        var el = document.getElementById('countdown');
        setInterval(function() {
            seconds--;
            if (seconds >= 0) el.textContent = seconds;
        }, 1000);
    </script>
</body>
</html>
