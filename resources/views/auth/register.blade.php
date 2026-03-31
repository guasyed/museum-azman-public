<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Museum Azman</title>
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
        <h1 class="museum-page-title text-3xl!">Create Account</h1>
        <p class="museum-page-subtitle">New registrations require admin approval.</p>

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.perform') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <label class="museum-field">
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>

            <label class="museum-field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>

            <label class="museum-field">
                <span>Requested Role</span>
                <select name="role_id" required>
                    <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Select your requested role</option>
                    @foreach(($requestableRoles ?? collect()) as $role)
                        <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="museum-field">
                <span>Profile Picture (optional)</span>
                <input type="file" name="avatar" accept="image/*,.webp">
            </label>

            <label class="museum-field">
                <span>Password</span>
                <input type="password" name="password" required>
            </label>

            <label class="museum-field">
                <span>Confirm Password</span>
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit" class="museum-btn w-full">Submit Registration</button>
        </form>

        <p class="mt-3 text-xs text-zinc-500">Requested role access is reviewed by admin during approval.</p>

        <p class="mt-4 text-sm text-zinc-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold" style="color: var(--museum-accent);">Sign in</a>
        </p>
    </div>
</body>
</html>
