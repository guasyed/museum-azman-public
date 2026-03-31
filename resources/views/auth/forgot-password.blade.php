<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Museum Azman</title>
    <link rel="icon" type="image/x-icon" href="/icons/museum-azman.ico?v=3">
    <link rel="alternate icon" href="/icons/museum-azman.ico?v=3">
    @php
        $viteCss = \Illuminate\Support\Facades\Vite::asset('resources/css/app.css');
        $viteJs = \Illuminate\Support\Facades\Vite::asset('resources/js/app.js');
        $toRelativeAsset = static function (string $url): string {
            $path = (string) parse_url($url, PHP_URL_PATH);
            $query = (string) parse_url($url, PHP_URL_QUERY);

            return $query !== '' ? $path.'?'.$query : $path;
        };
    @endphp
    <link rel="stylesheet" href="{{ $toRelativeAsset($viteCss) }}">
    <script type="module" src="{{ $toRelativeAsset($viteJs) }}"></script>
</head>
<body class="museum-shell flex min-h-screen items-center justify-center bg-[#f6f5f4] p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
        <h1 class="museum-page-title text-3xl!">Forgot Password</h1>
        <p class="museum-page-subtitle">Enter your email to receive a reset link.</p>

        @if(session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <label class="museum-field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>

            <button type="submit" class="museum-btn w-full">Send Reset Link</button>
        </form>

        <p class="mt-4 text-sm text-zinc-600">
            Back to login?
            <a href="{{ route('login') }}" class="font-semibold" style="color: var(--museum-accent);">Sign in</a>
        </p>
    </div>
</body>
</html>
