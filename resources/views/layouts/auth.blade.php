<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - DFTM Solutions</title>
    <link rel="stylesheet" href="{{ asset('css/dftm-theme.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, #0A2540 0%, #001235 50%, #000B21 100%);
            padding: 24px;
        }
        .auth-card {
            background: #FFFFFF;
            width: 100%;
            max-width: 440px;
            border-radius: var(--radius-lg);
            padding: 36px 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .auth-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .auth-logo {
            max-height: 52px;
            width: auto;
            margin-bottom: 12px;
        }
        .auth-subtitle {
            font-size: 0.85rem;
            color: var(--dftm-slate);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
