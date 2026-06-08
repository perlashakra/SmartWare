<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f7;
            color: #51545e;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }
        .wrapper {
            background-color: #f4f4f7;
            width: 100%;
            padding: 45px 0;
        }
        .content {
            max-width: 570px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 45px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-height: 75px;
            width: auto;
        }
        h1 {
            color: #333333;
            font-size: 19px;
            font-weight: bold;
            margin-top: 0;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            color: #51545e;
        }
        .button-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .btn-primary {
            background-color: #e53e3e; /* Distinctive color choice for security actions */
            color: #ffffff !important;
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #b0adc5;
        }
        .sub {
            border-top: 1px solid #e8e5ef;
            margin-top: 25px;
            padding-top: 25px;
            font-size: 12px;
        }
        .break-all {
            word-break: break-all;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="content">
        <div class="header">
            @if(file_exists(public_path('images/storexLogo.png')))
                <img src="{{ $message->embed(public_path('images/storexLogo.png')) }}" alt="StoreX Logo" class="logo">
            @else
                <h2>StoreX</h2>
            @endif
        </div>

        <h1>{{ __('auth.hello') ?? 'Hello!' }}</h1>
        <p>{{ __('auth.reset_password_body') ?? 'You are receiving this email because we received a password reset request for your account.' }}</p>

        <div class="button-wrapper">
            <a href="{{ $url }}" class="btn-primary" target="_blank">
                {{ __('auth.reset_password_button') ?? 'Reset Password' }}
            </a>
        </div>

        <p>{{ __('auth.reset_password_expire_notice', ['count' => 60]) ?? 'This password reset link will expire in 60 minutes.' }}</p>
        <p>{{ __('auth.reset_password_ignore') ?? 'If you did not request a password reset, no further action is required.' }}</p>

        <p>
            {{ __('auth.regards') ?? 'Regards' }},<br>
            <strong>StoreX</strong>
        </p>

        <div class="sub">
            <p>{{ __('auth.trouble_clicking') ?? 'If you\'re having trouble clicking the button, copy and paste the URL below into your web browser:' }}</p>
            <p class="break-all"><a href="{{ $url }}">{{ $url }}</a></p>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} StoreX. All rights reserved.</p>
    </div>
</div>
</body>
</html>
