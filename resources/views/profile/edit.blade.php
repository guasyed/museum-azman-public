<x-layout title="My Profile - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">My Profile</h2>
            <p class="museum-page-subtitle">Update your account details and password.</p>
        </div>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Profile Details</h3>
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')

                <div class="md:col-span-2 flex items-center gap-4">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full object-cover border border-zinc-300">
                    @else
                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-full text-lg font-semibold text-white" style="background: var(--museum-accent);">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    @endif

                    <label class="museum-field flex-1">
                        <span>Profile Picture</span>
                        <input type="file" name="avatar" accept="image/*,.webp">
                    </label>
                </div>

                <label class="museum-field">
                    <span>Name</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </label>

                <label class="museum-field">
                    <span>Email</span>
                    @if($user->isAdmin())
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @else
                        <input type="email" value="{{ $user->email }}" disabled>
                        <small class="text-xs text-zinc-500">Only admin users can update email addresses.</small>
                    @endif
                </label>

                <div class="md:col-span-2">
                    <button type="submit" class="museum-btn">Save Profile</button>
                </div>
            </form>
        </article>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Change Password</h3>
            <form action="{{ route('profile.password.update') }}" method="POST" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')

                <label class="museum-field md:col-span-2">
                    <span>Current Password</span>
                    <input type="password" name="current_password" required>
                </label>

                <label class="museum-field">
                    <span>New Password</span>
                    <input type="password" name="password" required>
                </label>

                <label class="museum-field">
                    <span>Confirm New Password</span>
                    <input type="password" name="password_confirmation" required>
                </label>

                <div class="md:col-span-2">
                    <button type="submit" class="museum-btn">Update Password</button>
                </div>
            </form>
        </article>

    </section>
</x-layout>
