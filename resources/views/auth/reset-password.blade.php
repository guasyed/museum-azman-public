<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Museum Azman</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('icons/museum-azman.ico') }}?v=3">
    <link rel="alternate icon" href="{{ asset('icons/museum-azman.ico') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="museum-shell flex min-h-screen items-center justify-center bg-[#f6f5f4] p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
        <h1 class="museum-page-title text-3xl!">Reset Password</h1>
        <p class="museum-page-subtitle">Set a new password for your account.</p>

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="museum-field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
            </label>

            <label class="museum-field">
                <span>New Password</span>
                <input type="password" name="password" required>
            </label>

            <label class="museum-field">
                <span>Confirm Password</span>
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit" class="museum-btn w-full">Reset Password</button>
        </form>
    </div>
</body>
</html>
