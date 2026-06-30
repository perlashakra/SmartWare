<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Code</title>
</head>
<body style="font-family: sans-serif; padding: 20px; color: #333;">
<h2>{{ __('auth.reset_password_heading') }}</h2>
<p>{{ $user->first_name }},</p>
<p>{{ __('auth.reset_password_body') }}</p>

<div style="background: #f4f4f4; padding: 15px; font-size: 24px; font-weight: bold; letter-spacing: 4px; text-align: center; margin: 20px 0; border-radius: 5px;">
    {{ $otp }}
</div>

<p>{{ __('auth.reset_password_ignore') }}</p>
</body>
</html>
