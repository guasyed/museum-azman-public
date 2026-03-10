<x-layout title="Manage Users - Museum Azman">
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">User Management</h2>
                <p class="museum-page-subtitle">Admin can manage profile picture and account data</p>
            </div>
            <a href="{{ route('settings.index', ['tab' => 'users-roles']) }}" class="museum-btn-secondary">Back to Settings</a>
        </div>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Create User</h3>
            <form id="create-user-form" action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="museum-field">
                    <span>Name</span>
                    <input type="text" name="name" required>
                </label>
                <label class="museum-field">
                    <span>Email</span>
                    <input type="email" name="email" required>
                </label>
                <label class="museum-field">
                    <span>Role</span>
                    <select name="role_id" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="museum-field">
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <label class="museum-field md:col-span-2">
                    <span>Profile Picture</span>
                    <input id="create-avatar-input" type="file" name="avatar" accept="image/*,.webp">
                </label>
                <div class="md:col-span-2">
                    <button type="submit" class="museum-btn">Create User</button>
                </div>
            </form>
        </article>

        <article class="museum-panel p-0! overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-245 text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 text-left text-zinc-600">
                            <th class="px-4 py-3">
                                @php
                                    $isNameSort = ($sortColumn ?? 'name') === 'name';
                                    $nextNameDirection = $isNameSort && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a
                                    href="{{ route('admin.users.index', ['sort' => 'name', 'direction' => $nextNameDirection]) }}"
                                    class="inline-flex items-center gap-1 hover:text-zinc-900"
                                >
                                    <span>User</span>
                                    @if($isNameSort)
                                        <span class="text-xs">{{ ($direction ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3">
                                @php
                                    $isEmailSort = ($sortColumn ?? 'name') === 'email';
                                    $nextEmailDirection = $isEmailSort && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a
                                    href="{{ route('admin.users.index', ['sort' => 'email', 'direction' => $nextEmailDirection]) }}"
                                    class="inline-flex items-center gap-1 hover:text-zinc-900"
                                >
                                    <span>Email</span>
                                    @if($isEmailSort)
                                        <span class="text-xs">{{ ($direction ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3">
                                @php
                                    $isRoleSort = ($sortColumn ?? 'name') === 'role';
                                    $nextRoleDirection = $isRoleSort && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a
                                    href="{{ route('admin.users.index', ['sort' => 'role', 'direction' => $nextRoleDirection]) }}"
                                    class="inline-flex items-center gap-1 hover:text-zinc-900"
                                >
                                    <span>Role</span>
                                    @if($isRoleSort)
                                        <span class="text-xs">{{ ($direction ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $user)
                        <tr class="border-b border-zinc-200">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-9 w-9 rounded-full object-cover">
                                    @else
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-zinc-900 text-xs font-semibold text-white">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    @endif
                                    <span class="font-semibold text-zinc-900">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-zinc-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $userRoleSlug = optional($user->roleRelation)->slug;
                                    $userRoleLabel = $user->role_label;
                                    $userRoleClass = in_array($userRoleSlug, ['owner', 'admin'], true)
                                        ? 'bg-zinc-900 text-white'
                                        : 'bg-zinc-100 text-zinc-700';
                                @endphp
                                <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $userRoleClass }}">{{ $userRoleLabel }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="museum-btn-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-4 text-zinc-500">No users found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('create-user-form');
    const input = document.getElementById('create-avatar-input');

    if (!form || !input) {
        return;
    }

    const toFile = (blob, originalName) => {
        const extension = blob.type === 'image/png' ? 'png' : 'jpg';
        const base = (originalName || 'avatar').replace(/\.[^/.]+$/, '');
        return new File([blob], `${base}.${extension}`, { type: blob.type, lastModified: Date.now() });
    };

    const loadImage = (file) => new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve(img);
        };
        img.onerror = (error) => {
            URL.revokeObjectURL(url);
            reject(error);
        };
        img.src = url;
    });

    const canvasToBlob = (canvas, mime, quality) => new Promise((resolve) => {
        canvas.toBlob((blob) => resolve(blob), mime, quality);
    });

    const compressAvatar = async (file) => {
        if (!file || !file.type.startsWith('image/')) {
            return file;
        }

        if (file.size <= 2 * 1024 * 1024) {
            return file;
        }

        const image = await loadImage(file);
        const maxSide = 1400;
        const ratio = Math.min(maxSide / Math.max(image.width, 1), maxSide / Math.max(image.height, 1), 1);
        const width = Math.max(1, Math.round(image.width * ratio));
        const height = Math.max(1, Math.round(image.height * ratio));

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const context = canvas.getContext('2d');
        if (!context) {
            return file;
        }

        context.drawImage(image, 0, 0, width, height);

        const png = await canvasToBlob(canvas, 'image/png', 0.9);
        const jpeg = await canvasToBlob(canvas, 'image/jpeg', 0.82);

        const candidates = [png, jpeg].filter((blob) => blob instanceof Blob);
        const best = candidates.sort((a, b) => a.size - b.size)[0];

        if (!best || best.size >= file.size) {
            return file;
        }

        return toFile(best, file.name);
    };

    form.addEventListener('submit', async (event) => {
        const currentFile = input.files && input.files[0] ? input.files[0] : null;
        if (!currentFile) {
            return;
        }

        event.preventDefault();

        try {
            const compressed = await compressAvatar(currentFile);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressed);
            input.files = dataTransfer.files;
        } catch (error) {
            console.error(error);
        }

        form.submit();
    });
});
    </script>
</x-layout>
