<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('SmartWare - Email Verified') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full text-center border border-slate-100">
    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
        <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>

    <div class="mb-6 flex justify-center">
        <img src="{{ asset('images/storexLogo.png') }}" alt="Welcome to SmartWare" class="h-32 w-auto object-contain rounded-lg">
    </div>

    <h1 class="text-2xl font-bold text-slate-800 mb-3">
        {{ __('SmartWare') }}
    </h1>

    <p class="text-slate-600 mb-6">
        {{ __('Your email address has been successfully verified. Welcome aboard!') }}
    </p>

    <a href="http://localhost:3000/login" class="inline-block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-xl transition duration-200 shadow-sm">
        {{ __('Go to Login') }}
    </a>
</div>

</body>
</html>
