<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - DFTM Solutions</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 40px 0;
        }
        .main-card {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 560px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #00205B 0%, #0A2540 100%);
            padding: 32px 24px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            color: #94a3b8;
            margin: 6px 0 0;
            font-size: 13px;
        }
        .content {
            padding: 36px 32px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #00205B;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(0, 32, 91, 0.25);
        }
        .notice-box {
            background-color: #f8fafc;
            border-left: 4px solid #00205B;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #64748b;
        }
        .link-fallback {
            font-size: 12px;
            color: #94a3b8;
            word-break: break-all;
            line-height: 1.5;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .link-fallback a {
            color: #0284c7;
            text-decoration: underline;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <div class="header">
                <h1>DFTM SOLUTIONS</h1>
                <p>Inventory & Repair Tracking System</p>
            </div>
            <div class="content">
                <div class="greeting">Hello {{ $user->name ?? 'User' }},</div>
                <p class="text">
                    Nakatanggap kami ng kahilingan na i-reset ang password para sa iyong DFTM Solutions account. 
                    I-click ang button sa ibaba upang mag-set ng bagong password:
                </p>

                <div class="btn-container">
                    <a href="{{ $resetUrl }}" class="btn" target="_blank">Reset Password</a>
                </div>

                <div class="notice-box">
                    <strong>Paalala:</strong> Ang link na ito ay may bisa lamang sa loob ng <strong>60 minuto</strong>. Kung hindi ikaw ang humiling ng pagbabagong ito, maaari mong balewalain ang email na ito at mananatiling ligtas ang iyong account.
                </div>

                <div class="link-fallback">
                    Kung nagkakaproblema ka sa pag-click sa button, kopyahin at i-paste ang URL na ito sa iyong browser:<br>
                    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                </div>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} DFTM Solutions. All rights reserved.<br>
                This is an automated system email. Please do not reply directly to this message.
            </div>
        </div>
    </div>
</body>
</html>
