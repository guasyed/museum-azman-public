<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Museum Azman</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('icons/museum-azman.ico') }}?v=3">
    <link rel="alternate icon" href="{{ asset('icons/museum-azman.ico') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="museum-shell flex min-h-screen items-center justify-center bg-[#f6f5f4] p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
        <h1 class="museum-page-title !text-3xl">Sign In</h1>
        <p class="museum-page-subtitle">Login as admin or user</p>

        <form method="POST" action="{{ route('login.perform') }}" class="mt-6 space-y-4">
            @csrf
            <label class="museum-field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>

            <label class="museum-field">
                <span>Password</span>
                <input type="password" name="password" required>
            </label>

            <label class="inline-flex items-center gap-2 text-sm text-zinc-600">
                <input type="checkbox" name="remember" class="rounded border-zinc-300">
                <span>Remember me</span>
            </label>

            <button type="submit" class="museum-btn w-full">Login</button>
        </form>

        <div class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs text-zinc-600">
            <p><strong>Admin:</strong> admin@museumazman.com / password</p>
            <p class="mt-1"><strong>User:</strong> user@museumazman.com / password</p>
        </div>
    </div>
</body>
</html>
