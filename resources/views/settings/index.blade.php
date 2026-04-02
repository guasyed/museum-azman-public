<x-layout title="Settings - Museum Azman">
    <style>
        .settings-tab:hover {
            color: var(--museum-accent);
        }
    </style>
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Settings</h2>
            <p class="museum-page-subtitle">System configuration and user management</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <p class="font-semibold">Unable to save your changes:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-8 border-b border-zinc-200">
    <nav class="flex flex-wrap gap-2 -mb-px" aria-label="Settings tabs">
        @php
            $tabs = ($canAccessAdminTabs ?? false)
                ? [
                    ['key' => 'general',       'label' => 'General',        'icon' => '⚙️'],
                    ['key' => 'users-roles',   'label' => 'Users & Roles',  'icon' => '👥'],
                    ['key' => 'notifications', 'label' => 'Notifications',  'icon' => '🔔'],
                    ['key' => 'appearance',    'label' => 'Appearance',     'icon' => '🎨'],
                ]
                : [
                    ['key' => 'notifications', 'label' => 'Notifications',  'icon' => '🔔'],
                    ['key' => 'appearance',    'label' => 'Appearance',     'icon' => '🎨'],
                ];
        @endphp

        @foreach($tabs as $tab)
            @php $isActive = $activeTab === $tab['key']; @endphp

            <a
                href="{{ route('settings.index', ['tab' => $tab['key']]) }}"
                class="
                    settings-tab
                    inline-flex items-center gap-2
                    px-4 py-3
                    border-b-2
                    text-sm font-semibold
                    transition-colors
                    {{ $isActive
                        ? 'border-current'
                        : 'border-transparent text-zinc-500'
                    }}
                "
                style="{{ $isActive ? 'color: var(--museum-accent);' : '' }}"
                aria-current="{{ $isActive ? 'page' : 'false' }}"
            >
                <span class="text-base leading-none">{{ $tab['icon'] }}</span>
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>

        @if($activeTab === 'general')
            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">Organization Information</h3>
                <p class="mt-1 text-sm text-zinc-600">Basic information about your collection</p>

                <form method="POST" action="{{ route('settings.update', ['section' => 'general'], false) }}" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    <fieldset @disabled(!($canManageSettings ?? false)) class="m-0 min-w-0 border-0 p-0">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-4">
                            <label class="museum-field">
                                <span>Organization Logo</span>
                                <input type="file" name="organization_logo" accept="image/*,.webp" class="museum-input">
                                @if(!empty($generalSettings['organization_logo_url']))
                                    <div class="mt-2">
                                        <img src="{{ $generalSettings['organization_logo_url'] }}" alt="Organization Logo" class="h-16 w-16 rounded-lg border border-zinc-300 object-cover">
                                    </div>
                                @endif
                            </label>

                            <label class="museum-field">
                                <span>Organization Name</span>
                                <input name="organization_name" value="{{ old('organization_name', $generalSettings['organization_name']) }}">
                            </label>
                            <label class="museum-field">
                                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
                                <span>Phone Number</span>
                                <input
                                    id="phone_number"
                                    name="phone_number"
                                    type="tel"
                                    value="{{ old('phone_number', $generalSettings['phone_number'] ?? '') }}"
                                    class="museum-input"
                                >
                            </label>
                            <label class="museum-field">
                                <span>Address</span>
                                <input name="address" value="{{ old('address', $generalSettings['address']) }}">
                            </label>
                        </div>

                        <div class="space-y-4">
                            <label class="museum-field">
                                <span>Contact Email</span>
                                <input type="email" name="contact_email" value="{{ old('contact_email', $generalSettings['contact_email']) }}">
                            </label>
                            <label class="museum-field">
                                <span>Timezone</span>
                               @php
                                $currentTz = old('timezone', $generalSettings['timezone'] ?? config('app.timezone'));
                                $regions = [
                                    'Asia' => DateTimeZone::ASIA,
                                    'Europe' => DateTimeZone::EUROPE,
                                    'America' => DateTimeZone::AMERICA,
                                    'Africa' => DateTimeZone::AFRICA,
                                    'Australia' => DateTimeZone::AUSTRALIA,
                                    'Pacific' => DateTimeZone::PACIFIC,
                                    'UTC' => DateTimeZone::UTC,
                                ];
                                @endphp

                                <select name="timezone" class="museum-input">

                                @foreach($regions as $region => $mask)
                                    <optgroup label="{{ $region }}">
                                        @foreach(DateTimeZone::listIdentifiers($mask) as $tz)

                                            @php
                                                $dt = new DateTime("now", new DateTimeZone($tz));
                                                $offset = $dt->getOffset();
                                                $hours = floor($offset / 3600);
                                                $minutes = abs(($offset % 3600) / 60);
                                                $gmt = sprintf('GMT%+d:%02d', $hours, $minutes);

                                                $city = str_replace('_', ' ', explode('/', $tz)[1] ?? $tz);
                                            @endphp

                                            <option value="{{ $tz }}" @selected($currentTz === $tz)>
                                                {{ $gmt }} — {{ $city }}
                                            </option>

                                        @endforeach
                                    </optgroup>
                                @endforeach

                                </select>
                            </label>
                        </div>
                    </div>

                    @if($canManageSettings ?? false)
                        <button type="submit" class="museum-btn mt-5">Save Changes</button>
                    @endif
                    </fieldset>
                </form>
            </article>

            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">$ Currency & Regional Settings</h3>
                <p class="mt-1 text-sm text-zinc-600">Configure currency and regional preferences</p>

                <form method="POST" action="{{ route('settings.update', ['section' => 'regional'], false) }}" class="mt-5">
                    @csrf
                    <fieldset @disabled(!($canManageSettings ?? false)) class="m-0 min-w-0 border-0 p-0">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="museum-field">
                            <span>Default Currency</span>
                            <select name="currency">
                                <option value="USD" @selected(old('currency', $regionalSettings['currency']) === 'USD')>USD ($)</option>
                                <option value="MYR" @selected(old('currency', $regionalSettings['currency']) === 'MYR')>MYR (RM)</option>
                                <option value="EUR" @selected(old('currency', $regionalSettings['currency']) === 'EUR')>EUR (€)</option>
                                <option value="GBP" @selected(old('currency', $regionalSettings['currency']) === 'GBP')>GBP (£)</option>
                                <option value="SGD" @selected(old('currency', $regionalSettings['currency']) === 'SGD')>SGD (S$)</option>
                            </select>
                        </label>

                        <label class="museum-field">
                            <span>Date Format</span>
                            <select name="date_format">
                                <option value="Y-m-d" @selected(old('date_format', $regionalSettings['date_format']) === 'Y-m-d')>YYYY-MM-DD</option>
                                <option value="d/m/Y" @selected(old('date_format', $regionalSettings['date_format']) === 'd/m/Y')>DD/MM/YYYY</option>
                                <option value="m/d/Y" @selected(old('date_format', $regionalSettings['date_format']) === 'm/d/Y')>MM/DD/YYYY</option>
                                <option value="d M Y" @selected(old('date_format', $regionalSettings['date_format']) === 'd M Y')>DD MMM YYYY</option>
                            </select>
                        </label>
                    </div>

                    @if($canManageSettings ?? false)
                        <button type="submit" class="museum-btn mt-5">Save Changes</button>
                    @endif
                    </fieldset>
                </form>
            </article>

            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">⛁ Data & Backup</h3>
                <p class="mt-1 text-sm text-zinc-600">Manage your data and backup settings</p>

                <div class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-zinc-900">Automatic Backups</p>
                            <p class="mt-1 text-sm text-zinc-600">
                                Last backup:
                                @if(!empty($backupMeta['last_generated_at']))
                                    {{ \Illuminate\Support\Carbon::parse($backupMeta['last_generated_at'])->timezone($backupMeta['timezone'] ?? config('app.timezone'))->format('M j, Y \a\t g:i A') }}
                                @else
                                    Not generated yet
                                @endif
                            </p>
                            @if(!empty($backupMeta['last_file']))
                                <p class="mt-1 text-xs text-zinc-500">File: {{ basename((string) $backupMeta['last_file']) }}</p>
                            @endif
                            <p class="mt-1 text-xs text-zinc-500">Timezone: {{ $backupMeta['timezone'] ?? config('app.timezone') }}</p>
                        </div>
                        <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ ($backupSettings['enabled'] ?? true) ? 'bg-zinc-900 text-white' : 'bg-zinc-200 text-zinc-700' }}">
                            {{ ($backupSettings['enabled'] ?? true) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.update', ['section' => 'backup'], false) }}" class="mt-4 grid gap-4 rounded-xl border border-zinc-200 bg-white p-4 md:grid-cols-3">
                    @csrf
                    <fieldset @disabled(!($canManageSettings ?? false)) class="m-0 min-w-0 border-0 p-0 contents">
                    <label class="museum-field md:col-span-1">
                        <span>Auto Backup</span>
                        <input type="hidden" name="backup_auto_enabled" value="0">
                        <select name="backup_auto_enabled">
                            <option value="1" @selected(old('backup_auto_enabled', ($backupSettings['enabled'] ?? true) ? '1' : '0') === '1')>Enabled</option>
                            <option value="0" @selected(old('backup_auto_enabled', ($backupSettings['enabled'] ?? true) ? '1' : '0') === '0')>Disabled</option>
                        </select>
                    </label>

                    <label class="museum-field md:col-span-1">
                        <span>Run Time (Daily)</span>
                        <input type="time" name="backup_auto_time" value="{{ old('backup_auto_time', $backupSettings['time'] ?? '03:00') }}" required>
                    </label>

                    <div class="md:col-span-3">
                        @if($canManageSettings ?? false)
                            <button style="margin-top:29px;" type="submit" class="museum-btn w-full">Save Auto Backup</button>
                        @endif
                    </div>
                    </fieldset>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if($canManageSettings ?? false)
                        <form method="POST" action="{{ route('settings.backup.generate', [], false) }}">
                            @csrf
                            <button type="submit" class="museum-btn">Generate Backup</button>
                        </form>

                        @if(!empty($backupMeta['has_file']))
                            <a href="{{ route('settings.backup.download') }}" class="museum-btn-secondary">Download Backup</a>
                        @else
                            <button type="button" class="museum-btn-secondary opacity-60" disabled>Download Backup</button>
                        @endif
                    @else
                        <button type="button" class="museum-btn opacity-60" disabled>Generate Backup</button>
                        <button type="button" class="museum-btn-secondary opacity-60" disabled>Download Backup</button>
                    @endif

                    <button type="button" class="museum-btn-secondary">Restore from Backup</button>
                </div>

                <div class="mt-4 overflow-x-auto rounded-xl border border-zinc-200 bg-white">
                    <table class="w-full min-w-180 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                <th class="px-4 py-3">File</th>
                                <th class="px-4 py-3">Generated At</th>
                                <th class="px-4 py-3 text-right">Size</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($backupList as $backup)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-zinc-900">{{ $backup['file_name'] }}</td>
                                    <td class="px-4 py-3 text-zinc-600">{{ $backup['generated_at_display'] }}</td>
                                    <td class="px-4 py-3 text-right text-zinc-600">{{ number_format((int) $backup['size_kb']) }} KB</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($canManageSettings ?? false)
                                                <a href="{{ route('settings.backup.download', ['file' => $backup['file_name']]) }}" class="museum-btn-secondary">Download</a>
                                                <form method="POST" action="{{ route('settings.backup.delete', [], false) }}" onsubmit="return confirm('Delete this backup file?');">
                                                    @csrf
                                                    <input type="hidden" name="file" value="{{ $backup['file_name'] }}">
                                                    <button type="submit" class="museum-btn-secondary text-rose-600">Delete</button>
                                                </form>
                                            @else
                                                <button type="button" class="museum-btn-secondary opacity-60" disabled>Download</button>
                                                <button type="button" class="museum-btn-secondary opacity-60" disabled>Delete</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-zinc-500">No backup files yet. Generate your first backup above.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        @elseif($activeTab === 'users-roles')
            <article class="museum-panel p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="museum-section-title text-base!">◉ User Management</h3>
                        <p class="mt-1 text-sm text-zinc-600">Manage users and their access permissions</p>
                    </div>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="museum-btn text-xs">+ Add User</a>
                    @endif
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-245 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                <th class="py-2">
                                    @php
                                        $isUserNameSort = ($userSortColumn ?? 'name') === 'name';
                                        $nextUserNameDirection = $isUserNameSort && ($userDirection ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                    @endphp
                                    <a
                                        href="{{ route('settings.index', array_merge(request()->query(), ['tab' => 'users-roles', 'user_sort' => 'name', 'user_direction' => $nextUserNameDirection])) }}"
                                        class="inline-flex items-center gap-1 hover:text-zinc-900"
                                    >
                                        <span>Name</span>
                                        @if($isUserNameSort)
                                            <span class="text-xs">{{ ($userDirection ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2">
                                    @php
                                        $isUserEmailSort = ($userSortColumn ?? 'name') === 'email';
                                        $nextUserEmailDirection = $isUserEmailSort && ($userDirection ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                    @endphp
                                    <a
                                        href="{{ route('settings.index', array_merge(request()->query(), ['tab' => 'users-roles', 'user_sort' => 'email', 'user_direction' => $nextUserEmailDirection])) }}"
                                        class="inline-flex items-center gap-1 hover:text-zinc-900"
                                    >
                                        <span>Email</span>
                                        @if($isUserEmailSort)
                                            <span class="text-xs">{{ ($userDirection ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2">
                                    @php
                                        $isUserRoleSort = ($userSortColumn ?? 'name') === 'role';
                                        $nextUserRoleDirection = $isUserRoleSort && ($userDirection ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                    @endphp
                                    <a
                                        href="{{ route('settings.index', array_merge(request()->query(), ['tab' => 'users-roles', 'user_sort' => 'role', 'user_direction' => $nextUserRoleDirection])) }}"
                                        class="inline-flex items-center gap-1 hover:text-zinc-900"
                                    >
                                        <span>Role</span>
                                        @if($isUserRoleSort)
                                            <span class="text-xs">{{ ($userDirection ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2">
                                    @php
                                        $isUserStatusSort = ($userSortColumn ?? 'name') === 'status';
                                        $nextUserStatusDirection = $isUserStatusSort && ($userDirection ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                    @endphp
                                    <a
                                        href="{{ route('settings.index', array_merge(request()->query(), ['tab' => 'users-roles', 'user_sort' => 'status', 'user_direction' => $nextUserStatusDirection])) }}"
                                        class="inline-flex items-center gap-1 hover:text-zinc-900"
                                    >
                                        <span>Status</span>
                                        @if($isUserStatusSort)
                                            <span class="text-xs">{{ ($userDirection ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2">Last Login</th>
                                <th class="py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php
                                    $nameParts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
                                    $initials = collect($nameParts)
                                        ->take(2)
                                        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                        ->implode('');
                                    $initials = $initials !== '' ? $initials : 'U';

                                    $rawRole = strtolower((string) optional($user->roleRelation)->slug);
                                    $roleLabel = $user->role_label;
                                    $isPrivilegedRole = in_array($rawRole, ['admin', 'owner'], true);

                                    $lastLogin = $user->updated_at
                                        ? \App\Support\DateFormat::display($user->updated_at) . ' ' . $user->updated_at->format('h:i A')
                                        : '-';
                                @endphp
                                <tr class="border-b border-zinc-200 last:border-b-0">
                                    <td class="py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-semibold text-white" style="background: var(--museum-accent);">{{ $initials }}</span>
                                            <span class="font-medium text-zinc-900">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-zinc-600">{{ $user->email }}</td>
                                    <td class="py-3">
                                        <span
                                            class="rounded-md px-2 py-0.5 text-[11px] font-semibold"
                                            style="{{ $isPrivilegedRole
                                                ? 'background: var(--museum-accent); color: #fff;'
                                                : 'background: color-mix(in srgb, var(--museum-accent) 14%, white); color: var(--museum-accent); border: 1px solid color-mix(in srgb, var(--museum-accent) 35%, white);' }}"
                                        >{{ $roleLabel }}</span>
                                    </td>
                                    <td class="py-3">
                                        @if($user->isApproved())
                                            <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Approved</span>
                                        @else
                                            <span class="rounded-md border px-2 py-0.5 text-[11px] font-semibold" style="border-color: color-mix(in srgb, var(--museum-accent) 35%, white); background: color-mix(in srgb, var(--museum-accent) 12%, white); color: var(--museum-accent);">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-zinc-600">{{ $lastLogin }}</td>
                                    <td class="py-3 text-right">
                                        @if(auth()->check() && auth()->user()->isAdmin())
                                            <a href="{{ route('admin.users.index') }}" class="text-zinc-700 hover:text-zinc-900">Manage</a>
                                        @else
                                            <span class="text-zinc-400">View</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-zinc-500">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">◌ Role Permissions</h3>
                <p class="mt-1 text-sm text-zinc-600">Overview of permissions by user role</p>

                <div class="mt-4 space-y-3">
                    @forelse($roles as $role)
                        @php
                            $permissions = is_array($role->permissions) ? $role->permissions : [];
                            $firstColumn = array_slice($permissions, 0, (int) ceil(max(count($permissions), 1) / 2));
                            $secondColumn = array_slice($permissions, count($firstColumn));
                            $isDarkBadge = in_array($role->slug, ['owner', 'admin'], true);
                        @endphp
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $isDarkBadge ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-700' }}">{{ $role->name }}</span>
                                <p class="text-xs text-zinc-500">{{ $role->description }}</p>
                            </div>
                            <div class="grid gap-2 text-xs text-zinc-700 md:grid-cols-2">
                                <ul class="list-disc space-y-1 pl-4">
                                    @foreach($firstColumn as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                <ul class="list-disc space-y-1 pl-4">
                                    @foreach($secondColumn as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-500">No role definitions found.</div>
                    @endforelse
                </div>
            </article>
        @elseif($activeTab === 'notifications')
            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">◌ Notification Preferences</h3>
                <p class="mt-1 text-sm text-zinc-600">Configure when and how you receive notifications</p>

                <form method="POST" action="{{ route('settings.update', ['section' => 'notifications'], false) }}" class="mt-5">
                    @csrf
                    <fieldset class="m-0 min-w-0 border-0 p-0">
                    <div class="space-y-3">
                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Movement Alerts</p>
                                <p class="text-sm text-zinc-600">Get notified when artworks are moved</p>
                            </div>
                            <input type="hidden" name="movement_alerts" value="0">
                            <input type="checkbox" name="movement_alerts" value="1" @checked(old('movement_alerts', $notificationSettings['movement_alerts'] ? '1' : '0') === '1') class="h-4 w-4 rounded border-zinc-300">
                        </label>

                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Insurance Expiry Warnings</p>
                                <p class="text-sm text-zinc-600">Alert 30 days before insurance expiration</p>
                            </div>
                            <input type="hidden" name="insurance_expiry" value="0">
                            <input type="checkbox" name="insurance_expiry" value="1" @checked(old('insurance_expiry', $notificationSettings['insurance_expiry'] ? '1' : '0') === '1') class="h-4 w-4 rounded border-zinc-300">
                        </label>

                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Loan Return Due</p>
                                <p class="text-sm text-zinc-600">Reminder when loaned artworks are due back</p>
                            </div>
                            <input type="hidden" name="loan_return_due" value="0">
                            <input type="checkbox" name="loan_return_due" value="1" @checked(old('loan_return_due', $notificationSettings['loan_return_due'] ? '1' : '0') === '1') class="h-4 w-4 rounded border-zinc-300">
                        </label>

                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Restoration Due</p>
                                <p class="text-sm text-zinc-600">Notifications for scheduled restoration work</p>
                            </div>
                            <input type="hidden" name="restoration_due" value="0">
                            <input type="checkbox" name="restoration_due" value="1" @checked(old('restoration_due', $notificationSettings['restoration_due'] ? '1' : '0') === '1') class="h-4 w-4 rounded border-zinc-300">
                        </label>

                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Valuation Updates</p>
                                <p class="text-sm text-zinc-600">Alert when artwork valuations change significantly</p>
                            </div>
                            <input type="hidden" name="valuation_updates" value="0">
                            <input type="checkbox" name="valuation_updates" value="1" @checked(old('valuation_updates', $notificationSettings['valuation_updates'] ? '1' : '0') === '1') class="h-4 w-4 rounded border-zinc-300">
                        </label>
                    </div>

                    <div class="mt-4 border-t border-zinc-200 pt-4">
                        <p class="font-medium text-zinc-800">Notification Delivery</p>
                        <div class="mt-3 space-y-2 text-sm">
                            <label class="inline-flex items-center gap-2 text-zinc-700">
                                <input type="hidden" name="delivery_email" value="0">
                                <input type="checkbox" name="delivery_email" value="1" @checked(old('delivery_email', $notificationSettings['delivery_email'] ? '1' : '0') === '1') class="rounded border-zinc-300">
                                <span>Email Notifications</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-zinc-700">
                                <input type="hidden" name="delivery_browser" value="0">
                                <input type="checkbox" name="delivery_browser" value="1" @checked(old('delivery_browser', $notificationSettings['delivery_browser'] ? '1' : '0') === '1') class="rounded border-zinc-300">
                                <span>Browser Notifications</span>
                            </label>
                        </div>
                        <button type="submit" class="mt-4 museum-btn">Save Preferences</button>
                    </div>
                    </fieldset>
                </form>
            </article>
        @elseif($activeTab === 'appearance')
            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">◔ Display Preferences</h3>
                <p class="mt-1 text-sm text-zinc-600">Customize the look and feel of the application</p>

                <form method="POST" action="{{ route('settings.update', ['section' => 'appearance'], false) }}" class="mt-5 space-y-4">
                    @csrf
                    <fieldset class="m-0 min-w-0 border-0 p-0 space-y-4">
                    @php
                        $accentCandidate = (string) old('accent_color', $appearanceSettings['accent_color'] ?? '#1c1917');
                        $selectedAccent = preg_match('/^#[0-9A-Fa-f]{6}$/', $accentCandidate) ? strtolower($accentCandidate) : '#1c1917';
                        $selectedHeadingFont = old('heading_font', $appearanceSettings['heading_font'] ?? 'cormorant');
                        $selectedBodyFont = old('body_font', $appearanceSettings['body_font'] ?? 'inter');
                        $fontOptions = [
                            'cormorant' => 'Cormorant Garamond',
                            'playfair' => 'Playfair Display',
                            'lora' => 'Lora',
                            'inter' => 'Inter',
                            'manrope' => 'Manrope',
                        ];
                    @endphp
                    <div>
                        <p class="font-semibold text-zinc-800">Theme</p>
                        <select name="theme" class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5">
                            <option value="light" @selected(old('theme', $appearanceSettings['theme']) === 'light')>Light Mode</option>
                            <option value="dark" @selected(old('theme', $appearanceSettings['theme']) === 'dark')>Dark Mode</option>
                        </select>
                        <p class="mt-2 text-sm text-zinc-500">Dark mode variant available for reduced eye strain</p>
                    </div>

                    <div>
                        <p class="font-semibold text-zinc-800">Display Density</p>
                        <select name="density" class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5">
                            <option value="comfortable" @selected(old('density', $appearanceSettings['density']) === 'comfortable')>Comfortable</option>
                            <option value="compact" @selected(old('density', $appearanceSettings['density']) === 'compact')>Compact</option>
                            <option value="spacious" @selected(old('density', $appearanceSettings['density']) === 'spacious')>Spacious</option>
                        </select>
                    </div>

                    <div>
                        <p class="font-semibold text-zinc-800">Typography</p>
                        <div class="mt-2 grid gap-3 md:grid-cols-2">
                            <label class="museum-field">
                                <span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Headings & Titles</span>
                                <select name="heading_font">
                                    @foreach($fontOptions as $fontValue => $fontLabel)
                                        <option value="{{ $fontValue }}" @selected($selectedHeadingFont === $fontValue)>{{ $fontLabel }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="museum-field">
                                <span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Body Text</span>
                                <select name="body_font">
                                    @foreach($fontOptions as $fontValue => $fontLabel)
                                        <option value="{{ $fontValue }}" @selected($selectedBodyFont === $fontValue)>{{ $fontLabel }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="mt-3 space-y-2">
                            <div class="flex items-center justify-between rounded-lg bg-zinc-100 px-3 py-2 text-sm">
                                <span class="text-zinc-600">Headings & Titles</span>
                                <span class="text-zinc-500">{{ $fontOptions[$selectedHeadingFont] ?? 'Cormorant Garamond' }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-zinc-100 px-3 py-2 text-sm">
                                <span class="text-zinc-600">Body Text</span>
                                <span class="text-zinc-500">{{ $fontOptions[$selectedBodyFont] ?? 'Inter' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 pt-4">
                        <p class="font-semibold text-zinc-800">Accent Color</p>
                        <div class="mt-3 flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2">
                            <input id="accent_color" type="color" name="accent_color" value="{{ $selectedAccent }}" class="h-10 w-14 cursor-pointer rounded border border-zinc-300 bg-white p-1">
                            <div>
                                <p class="font-semibold text-zinc-900">Custom Accent</p>
                                <p id="accent_color_label" class="text-xs text-zinc-500">Selected: {{ strtoupper($selectedAccent) }}</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="mt-2 museum-btn">Save Preferences</button>
                    </fieldset>
                </form>
            </article>
        @endif

        <script>
            (function () {
                const accentInput = document.getElementById('accent_color');
                const accentLabel = document.getElementById('accent_color_label');

                if (!accentInput || !accentLabel) {
                    return;
                }

                const applyPreview = () => {
                    const nextColor = (accentInput.value || '').toUpperCase();
                    if (!nextColor) {
                        return;
                    }

                    accentLabel.textContent = `Selected: ${nextColor}`;
                    document.body.style.setProperty('--museum-accent', nextColor.toLowerCase());
                };

                accentInput.addEventListener('input', applyPreview);
                accentInput.addEventListener('change', applyPreview);
            })();
        </script>
    </section>
</x-layout>
