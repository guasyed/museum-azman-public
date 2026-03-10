<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $uiTheme = 'light';
        $uiDensity = 'comfortable';
        $uiAccent = '#1c1917';
        $uiHeadingFontKey = 'cormorant';
        $uiBodyFontKey = 'inter';
        $fontFamilies = [
            'cormorant' => "'Cormorant Garamond', serif",
            'playfair' => "'Playfair Display', serif",
            'lora' => "'Lora', serif",
            'inter' => "'Inter', sans-serif",
            'manrope' => "'Manrope', sans-serif",
        ];
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $appearanceSettings = \App\Models\Setting::query()
                ->whereIn('key', ['theme', 'density', 'accent_color', 'heading_font', 'body_font'])
                ->pluck('value', 'key');
	
            $themeValue = (string) ($appearanceSettings->get('theme') ?? 'light');
            $densityValue = (string) ($appearanceSettings->get('density') ?? 'comfortable');
            $accentValue = (string) ($appearanceSettings->get('accent_color') ?? '#1c1917');
            $headingFontValue = (string) ($appearanceSettings->get('heading_font') ?? 'cormorant');
            $bodyFontValue = (string) ($appearanceSettings->get('body_font') ?? 'inter');
	
            $uiTheme = in_array($themeValue, ['light', 'dark'], true) ? $themeValue : 'light';
            $uiDensity = in_array($densityValue, ['comfortable', 'compact', 'spacious'], true) ? $densityValue : 'comfortable';
            $uiAccent = preg_match('/^#[0-9A-Fa-f]{6}$/', $accentValue) ? strtolower($accentValue) : '#1c1917';
            $uiHeadingFontKey = array_key_exists($headingFontValue, $fontFamilies) ? $headingFontValue : 'cormorant';
            $uiBodyFontKey = array_key_exists($bodyFontValue, $fontFamilies) ? $bodyFontValue : 'inter';
        }

        if (auth()->check()) {
            $currentUser = auth()->user();

            $userThemeValue = (string) ($currentUser->appearance_theme ?? '');
            $userDensityValue = (string) ($currentUser->appearance_density ?? '');
            $userAccentValue = (string) ($currentUser->appearance_accent_color ?? '');
            $userHeadingFontValue = (string) ($currentUser->appearance_heading_font ?? '');
            $userBodyFontValue = (string) ($currentUser->appearance_body_font ?? '');

            if (in_array($userThemeValue, ['light', 'dark'], true)) {
                $uiTheme = $userThemeValue;
            }

            if (in_array($userDensityValue, ['comfortable', 'compact', 'spacious'], true)) {
                $uiDensity = $userDensityValue;
            }

            if (preg_match('/^#[0-9A-Fa-f]{6}$/', $userAccentValue)) {
                $uiAccent = strtolower($userAccentValue);
            }

            if (array_key_exists($userHeadingFontValue, $fontFamilies)) {
                $uiHeadingFontKey = $userHeadingFontValue;
            }

            if (array_key_exists($userBodyFontValue, $fontFamilies)) {
                $uiBodyFontKey = $userBodyFontValue;
            }
        }

        $uiHeadingFontFamily = $fontFamilies[$uiHeadingFontKey];
        $uiBodyFontFamily = $fontFamilies[$uiBodyFontKey];
    @endphp
	<link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="{{ $uiTheme === 'dark' ? '#111827' : '#f7f7f6' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Museum Azman">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

    <title>{{ $title ?? 'Museum Azman' }}</title>
    @php
        $faviconHref = asset('icons/museum-azman.ico').'?v=3';
        $faviconType = 'image/x-icon';

        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $savedLogoPath = \App\Models\Setting::query()
                ->where('key', 'organization_logo_path')
                ->value('value');

            if (is_string($savedLogoPath) && trim($savedLogoPath) !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($savedLogoPath)) {
                $extension = strtolower(pathinfo($savedLogoPath, PATHINFO_EXTENSION));
                $faviconTypes = [
                    'svg' => 'image/svg+xml',
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    'ico' => 'image/x-icon',
                ];

                $faviconType = $faviconTypes[$extension] ?? 'image/png';
                $faviconHref = asset('storage/'.ltrim($savedLogoPath, '/')).'?v='.(string) \Illuminate\Support\Facades\Storage::disk('public')->lastModified($savedLogoPath);
            }
        }
    @endphp
    <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconHref }}">
    <link rel="alternate icon" href="{{ $faviconHref }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Lora:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
    @php
        $host = strtolower((string) request()->getHost());
        $isTunnelHost = str_contains($host, 'pinggy') || str_contains($host, 'ngrok') || str_contains($host, 'loca.lt');
        $isSettingsRoute = request()->routeIs('settings.*');
    @endphp

    @if($isTunnelHost && $isSettingsRoute)
        <link rel="stylesheet" href="{{ \Illuminate\Support\Facades\Vite::asset('resources/css/app.css') }}">
        <script type="module" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/js/app.js') }}"></script>
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body.museum-shell {
            --museum-accent: {{ $uiAccent }};
            --museum-font-heading: {!! $uiHeadingFontFamily !!};
            --museum-font-body: {!! $uiBodyFontFamily !!};
        }

        body.museum-shell .museum-btn {
            background: var(--museum-accent) !important;
            border-color: var(--museum-accent) !important;
        }

        body.museum-shell .museum-btn:hover {
            filter: brightness(0.92);
        }

        body.museum-shell .museum-page-title,
        body.museum-shell .museum-section-title,
        body.museum-shell .museum-brand-title {
            color: var(--museum-accent) !important;
        }

        body.museum-shell.museum-theme-dark {
            background: #0b1020 !important;
            color: #e5e7eb;
        }

        body.museum-shell.museum-theme-dark .museum-panel,
        body.museum-shell.museum-theme-dark .museum-card,
        body.museum-shell.museum-theme-dark .museum-stat-card,
        body.museum-shell.museum-theme-dark aside,
        body.museum-shell.museum-theme-dark header,
        body.museum-shell.museum-theme-dark .museum-modal {
            background: #111827 !important;
            border-color: #374151 !important;
            color: #e5e7eb;
        }

        body.museum-shell.museum-theme-dark .museum-page-subtitle,
        body.museum-shell.museum-theme-dark .text-zinc-600,
        body.museum-shell.museum-theme-dark .text-zinc-500 {
            color: #9ca3af !important;
        }

        body.museum-shell.museum-theme-dark .museum-field input,
        body.museum-shell.museum-theme-dark .museum-field select,
        body.museum-shell.museum-theme-dark .museum-field textarea {
            background: #0f172a !important;
            border-color: #4b5563 !important;
            color: #e5e7eb !important;
        }

        body.museum-shell.museum-density-compact .museum-panel,
        body.museum-shell.museum-density-compact .museum-card,
        body.museum-shell.museum-density-compact .museum-stat-card {
            padding: 0.75rem !important;
        }

        body.museum-shell.museum-density-compact .museum-field input,
        body.museum-shell.museum-density-compact .museum-field select,
        body.museum-shell.museum-density-compact .museum-field textarea {
            padding-top: 0.4rem !important;
            padding-bottom: 0.4rem !important;
        }

        body.museum-shell.museum-density-spacious .museum-panel,
        body.museum-shell.museum-density-spacious .museum-card,
        body.museum-shell.museum-density-spacious .museum-stat-card {
            padding: 1.5rem !important;
        }

        body.museum-shell.museum-density-spacious .museum-field input,
        body.museum-shell.museum-density-spacious .museum-field select,
        body.museum-shell.museum-density-spacious .museum-field textarea {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        /* Mobile Navigation Overlay */
        .mobile-nav {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.98);
            z-index: 1000;
            flex-direction: column;
            padding: 2rem;
        }

        .mobile-nav.active {
            display: flex;
        }

        .mobile-nav-link {
            font-size: 1.25rem;
            font-weight: 500;
            padding: 1rem 0;
            color: #18181b;
            border-bottom: 1px solid #e4e4e7;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .mobile-nav-link:hover {
            background-color: color-mix(in srgb, var(--museum-accent) 16%, white);
            color: var(--museum-accent);
        }

        .mobile-nav-link.active {
            color: var(--museum-accent);
            font-weight: 700;
            background-color: color-mix(in srgb, var(--museum-accent) 20%, white);
        }

        .museum-nav-item {
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .museum-nav-item:hover {
            background-color: color-mix(in srgb, var(--museum-accent) 16%, white);
            color: var(--museum-accent);
        }

        .museum-nav-item.active {
            background-color: color-mix(in srgb, var(--museum-accent) 20%, white) !important;
            border-color: color-mix(in srgb, var(--museum-accent) 38%, white) !important;
            color: var(--museum-accent) !important;
        }

        .logout-menu-item {
            color: #b91c1c;
        }

        .logout-menu-item:hover {
            background-color: #ffe4e6;
            color: #9f1239;
        }

        @media (max-width: 1024px) {
            .museum-shell {
                padding-top: 0;
            }
        }
		
		@media (min-width: 1024px) {
			.museum-brand-title {
				font-size: 1.4rem;
				font-weight: 700;
                color: var(--museum-accent);
			}
			
			/* Logo image styling */
			.lg\:flex .flex.items-center.gap-3 img {
				width: 30% !important;
				height: auto !important;
				max-width: 60px; /* Optional: prevents image from getting too large */
			}
			
			/* Alternative: more specific selector for the sidebar logo */
			aside .flex.items-center.gap-3 img {
				width: 30%;
				height: auto;
				max-width: 60px; /* Optional: prevents image from getting too large */
			}
			
			/* Fix for desktop sidebar */
			.lg\:grid {
				display: grid !important;
			}
			
			.lg\:hidden {
				display: none !important;
			}
			
			aside.lg\:flex {
				display: flex !important;
			}
		}
		
		/* Ensure main content takes full width on mobile */
		@media (max-width: 1023px) {
		    .lg\:grid {
		        display: block !important;
		    }
		    
		    aside.lg\:flex {
		        display: none !important;
		    }
		}
    </style>
</head>
<body class="museum-shell museum-theme-{{ $uiTheme }} museum-density-{{ $uiDensity }} bg-[#f6f5f4]">

<header class="sticky top-0 z-40 flex items-center justify-between border-b border-zinc-200 bg-[#f7f7f6] p-4 lg:hidden">
    <div class="flex items-center gap-3">
        @php
            $brandTitle = 'Art Collection';
            $brandLogoUrl = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $brandingSettings = \App\Models\Setting::query()
                    ->whereIn('key', ['organization_name', 'organization_logo_path'])
                    ->pluck('value', 'key');
                $savedOrganization = $brandingSettings->get('organization_name');
                $savedLogoPath = $brandingSettings->get('organization_logo_path');

                if (is_string($savedOrganization) && trim($savedOrganization) !== '') {
                    $brandTitle = trim($savedOrganization);
                }
                if (is_string($savedLogoPath) && trim($savedLogoPath) !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($savedLogoPath)) {
                    $brandLogoUrl = asset('storage/'.ltrim($savedLogoPath, '/'));
                }
            }
        @endphp
        @if($brandLogoUrl)
            <img src="{{ $brandLogoUrl }}" alt="logo" class="h-8 w-8 rounded-lg object-cover">
        @endif
        <p class="text-lg font-bold text-zinc-900">{{ $brandTitle }}</p>
    </div>
    <button id="hamburgerBtn" class="p-2 text-zinc-600">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
</header>

<div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">

    <aside class="hidden lg:flex min-h-screen flex-col border-r border-zinc-200 bg-[#f7f7f6]">
        <div class="border-b border-zinc-200 p-6">
            <div class="flex items-center gap-3">
                @if($brandLogoUrl)
                    <img src="{{ $brandLogoUrl }}" alt="logo" class="h-12 w-12 rounded-lg object-cover border border-zinc-300">
                @endif
                <p class="museum-brand-title">{{ $brandTitle }}</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 p-4 text-sm">
            <a href="{{ route('dashboard') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('artworks.index') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('artworks.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125 a1.64 1.64 0 0 1 1.668-1.668h1.996 c3.051 0 5.555-2.503 5.555-5.554 C21.965 6.012 17.461 2 12 2z"></path></svg>
                <span>Collection</span>
            </a>

            <a href="{{ route('movements.index') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('movements.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
                <span>Movement Tracker</span>
            </a>

            <a href="{{ route('locations.index') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('locations.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <span>Locations</span>
            </a>

            <a href="{{ route('artists.index') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('artists.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Artists</span>
            </a>

            <a href="{{ route('reports.index') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="M18 17V9"></path><path d="M13 17V5"></path><path d="M8 17v-3"></path></svg>
                <span>Reports & Analytics</span>
            </a>

            <a href="{{ route('settings.index') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <span>Settings</span>
            </a>

            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                    <span>Manage Users</span>
                </a>
                <a href="{{ route('admin.docs.technical') }}" class="museum-nav-item flex items-center gap-3 {{ request()->routeIs('admin.docs.technical') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>Technical Docs</span>
                </a>
            @endif
        </nav>

        <div class="mt-auto px-4 pb-6 pt-6 space-y-3">
            <p class="px-2 text-xs text-zinc-500">Management System v1.0</p>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto">
        <div class="mx-auto w-full max-w-350 p-6 lg:p-10">
            @auth
                @php
                    $notificationCount = 0;
                    $userNotifications = collect();
                    $pendingRegistrationNotifications = collect();

                    if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                        $notificationCount = auth()->user()->unreadNotifications()->count();
                        $notificationQuery = auth()->user()->notifications()->latest();
                        $userNotifications = (clone $notificationQuery)->take(12)->get();

                        if (auth()->user()->isAdmin()) {
                            $pendingRegistrationNotifications = auth()->user()
                            ->unreadNotifications()
                            ->where('type', \App\Notifications\NewUserRegistrationNotification::class)
                            ->latest()
                            ->take(8)
                            ->get();
                        }
                    }
                @endphp

                <div class="mb-6 flex items-center justify-end gap-3">
                    <details class="relative" id="notification-details">
                        <summary class="museum-btn-secondary inline-flex list-none items-center gap-2 cursor-pointer" title="Notifications" aria-label="Notifications">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path><path d="M10 17a2 2 0 0 0 4 0"></path></svg>
                            <span id="notification-count-badge" class="inline-flex min-w-6 items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background: var(--museum-accent);">{{ $notificationCount }}</span>
                        </summary>

                        <div class="absolute right-0 z-50 mt-2 rounded-xl border border-zinc-200 bg-white p-3 shadow-lg" style="width: 246px; max-width: 90vw;">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-sm font-semibold text-zinc-900">Notifications</p>
                            </div>

                            @if($userNotifications->isEmpty())
                                <p class="rounded-lg bg-zinc-50 px-3 py-2 text-xs text-zinc-500">No notification history yet.</p>
                            @else
                                <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                                    @foreach($userNotifications as $notification)
                                        @php
                                            $isRegistrationNotification = $notification->type === \App\Notifications\NewUserRegistrationNotification::class;
                                            $notificationUrl = $notification->data['url'] ?? null;
                                            if (! is_string($notificationUrl) || trim($notificationUrl) === '') {
                                                $notificationUrl = $isRegistrationNotification
                                                    ? route('admin.users.index', ['sort' => 'status', 'direction' => 'asc'])
                                                    : route('movements.index');
                                            }
                                        @endphp
                                        <a href="{{ $notificationUrl }}" class="block rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-3 text-[10px] text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-100">
                                            <p class="text-[10px] font-semibold leading-4 text-zinc-900">{{ $notification->data['title'] ?? ($notification->data['name'] ?? 'Notification') }}</p>
                                            <p class="mt-1 text-[10px] leading-4 text-zinc-600">{{ $notification->data['message'] ?? ($notification->data['email'] ?? 'Open to view details.') }}</p>
                                            @if(!empty($notification->data['updated_by']))
                                                <p class="mt-1 text-[10px] font-medium text-zinc-500">Updated By</p>
                                                <p class="text-[10px] font-semibold text-zinc-800">{{ $notification->data['updated_by'] }}</p>
                                            @endif
                                            @if(!empty($notification->data['assigned_by']))
                                                <p class="mt-1 text-[10px] font-medium text-zinc-500">Assigned By</p>
                                                <p class="text-[10px] font-semibold text-zinc-800">{{ $notification->data['assigned_by'] }}</p>
                                            @endif
                                            @if(!empty($notification->data['artwork_title']))
                                                <p class="mt-1 text-[10px] font-medium text-zinc-500">Art Work</p>
                                                <p class="text-[10px] font-semibold text-zinc-900">{{ $notification->data['artwork_title'] }}</p>
                                            @endif
                                            @if(!empty($notification->data['requested_role']))
                                                <p class="mt-1 text-[10px] font-medium text-zinc-500">Requested Role</p>
                                                <p class="text-[10px] font-semibold text-zinc-900">{{ $notification->data['requested_role'] }}</p>
                                            @endif
                                            <p class="mt-2 border-t border-zinc-200 pt-2 text-[10px] text-zinc-500">{{ $notification->created_at?->diffForHumans() }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if(auth()->user()->isAdmin() && $pendingRegistrationNotifications->isNotEmpty())
                                <div class="mt-3 border-t border-zinc-200 pt-2">
                                    <p class="text-xs font-semibold text-zinc-700">Pending registrations: {{ $pendingRegistrationNotifications->count() }}</p>
                                </div>
                            @endif
                        </div>
                    </details>

                    <a href="{{ route('profile.edit') }}" class="museum-btn-secondary inline-flex items-center gap-2">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="h-7 w-7 rounded-full object-cover">
                        @else
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold text-white" style="background: var(--museum-accent);">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        @endif
                        <span>Profile</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="museum-btn-secondary">Logout</button>
                    </form>
                </div>
            @endauth

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>
</div>

<div
    class="fixed right-0 top-1/2 z-50 flex -translate-y-1/2 flex-col gap-2"
    style="position:fixed; right:0; top:50%; transform:translateY(-50%); z-index:2147483000; display:flex; flex-direction:column; gap:8px; pointer-events:auto;"
>
    <button
        id="global-scroll-top-btn"
        type="button"
        class="inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border text-white shadow-lg transition"
        style="width:40px; height:40px; border-radius:9999px; border:1px solid var(--museum-accent); background:var(--museum-accent); color:#fff; box-shadow:0 6px 16px rgba(0,0,0,.25); cursor:pointer;"
        title="Back to top"
        aria-label="Back to top"
    >
        ↑
    </button>
    <button
        id="global-scroll-bottom-btn"
        type="button"
        class="inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border text-white shadow-lg transition"
        style="width:40px; height:40px; border-radius:9999px; border:1px solid var(--museum-accent); background:var(--museum-accent); color:#fff; box-shadow:0 6px 16px rgba(0,0,0,.25); cursor:pointer;"
        title="Go to bottom"
        aria-label="Go to bottom"
    >
        ↓
    </button>
</div>

<div id="mobileMenu" class="mobile-nav">
    <div class="flex items-center justify-between mb-8">
        <span class="text-xl font-bold">Menu</span>
        <button id="closeBtn" class="p-2 text-zinc-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <nav class="flex flex-col">
        <a href="{{ route('dashboard') }}" class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('artworks.index') }}" class="mobile-nav-link {{ request()->routeIs('artworks.*') ? 'active' : '' }}">Collection</a>
        <a href="{{ route('movements.index') }}" class="mobile-nav-link {{ request()->routeIs('movements.*') ? 'active' : '' }}">Movement Tracker</a>
        <a href="{{ route('locations.index') }}" class="mobile-nav-link {{ request()->routeIs('locations.*') ? 'active' : '' }}">Locations</a>
        <a href="{{ route('artists.index') }}" class="mobile-nav-link {{ request()->routeIs('artists.*') ? 'active' : '' }}">Artists</a>
        <a href="{{ route('reports.index') }}" class="mobile-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports & Analytics</a>
        <a href="{{ route('settings.index') }}" class="mobile-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">Settings</a>
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('settings.index', ['tab' => 'users-roles']) }}" class="mobile-nav-link {{ request()->routeIs('settings.*') && request()->string('tab')->toString() === 'users-roles' ? 'active' : '' }}">Users &amp; Roles</a>
            <a href="{{ route('admin.docs.technical') }}" class="mobile-nav-link {{ request()->routeIs('admin.docs.technical') ? 'active' : '' }}">Technical Docs</a>
            <a href="{{ route('admin.users.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Manage Users</a>
        @endif
        
        @auth
            <a href="{{ route('profile.edit') }}" class="mobile-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">My Profile</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-8">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-zinc-900 px-4 py-3 text-white font-semibold transition hover:bg-rose-700">Logout</button>
            </form>
        @endauth
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/intlTelInput.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Toggle Mobile Menu
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const closeBtn = document.getElementById('closeBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        const toggleMenu = () => {
            mobileMenu.classList.toggle('active');
            document.body.classList.toggle('overflow-hidden');
        };

        hamburgerBtn?.addEventListener('click', toggleMenu);
        closeBtn?.addEventListener('click', toggleMenu);

        // Close menu on link click
        document.querySelectorAll('.mobile-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                document.body.classList.remove('overflow-hidden');
            });
        });

        // Initialize Phone Input
        const input = document.querySelector("#phone_number");
        if (input && window.intlTelInput) {
            window.intlTelInput(input, {
                initialCountry: "my",
                separateDialCode: true,
                preferredCountries: ["my","sg","id","bn"],
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js"
            });
        }

        const initGlobalScrollButtons = () => {
            const scrollTopBtn = document.getElementById('global-scroll-top-btn');
            const scrollBottomBtn = document.getElementById('global-scroll-bottom-btn');
            if (!scrollTopBtn || !scrollBottomBtn) {
                return;
            }

            const scrollTolerance = 8;

            const bindHoverColor = (button) => {
                const accent = getComputedStyle(document.body).getPropertyValue('--museum-accent').trim() || '#1c1917';
                button.style.background = accent;
                button.style.borderColor = accent;

                button.addEventListener('mouseenter', () => {
                    if (button.disabled) {
                        return;
                    }

                    button.style.filter = 'brightness(0.9)';
                });

                button.addEventListener('mouseleave', () => {
                    button.style.filter = 'none';
                });
            };

            bindHoverColor(scrollTopBtn);
            bindHoverColor(scrollBottomBtn);

            const mainContainer = document.querySelector('main.overflow-y-auto');

            const getMainMaxScroll = () => {
                if (!mainContainer) {
                    return 0;
                }

                return Math.max(mainContainer.scrollHeight - mainContainer.clientHeight, 0);
            };

            const getWindowMaxScroll = () => {
                const scrollHeight = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
                return Math.max(scrollHeight - window.innerHeight, 0);
            };

            const getActiveScroller = () => {
                const mainMax = getMainMaxScroll();
                const windowMax = getWindowMaxScroll();

                if (mainContainer && mainMax > scrollTolerance && mainMax >= windowMax) {
                    return 'main';
                }

                if (windowMax > scrollTolerance) {
                    return 'window';
                }

                if (mainContainer && mainMax > scrollTolerance) {
                    return 'main';
                }

                return 'none';
            };

            const getScrollTop = () => {
                const active = getActiveScroller();
                return active === 'main' ? (mainContainer?.scrollTop ?? 0) : window.scrollY;
            };

            const getScrollHeight = () => {
                const active = getActiveScroller();
                return active === 'main'
                    ? (mainContainer?.scrollHeight ?? 0)
                    : Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
            };

            const getClientHeight = () => {
                const active = getActiveScroller();
                return active === 'main' ? (mainContainer?.clientHeight ?? 0) : window.innerHeight;
            };

            const syncVisibility = () => {
                const top = getScrollTop();
                const maxScroll = Math.max(getScrollHeight() - getClientHeight(), 0);
                const edgeThreshold = 24;
                const nearTop = top <= edgeThreshold;
                const nearBottom = top >= Math.max(maxScroll - edgeThreshold, 0);

                const activeScroller = getActiveScroller();
                const hasMeaningfulScroll = activeScroller !== 'none' && maxScroll > scrollTolerance;
                if (!hasMeaningfulScroll) {
                    // No meaningful scrolling available.
                    scrollTopBtn.style.display = 'none';
                    scrollBottomBtn.style.display = 'none';
                    return;
                }

                if (nearTop) {
                    // Top page: hide up and show down.
                    scrollTopBtn.style.display = 'none';
                    scrollBottomBtn.style.display = 'inline-flex';
                    return;
                }

                if (nearBottom) {
                    // Bottom page: show up, hide down.
                    scrollTopBtn.style.display = 'inline-flex';
                    scrollBottomBtn.style.display = 'none';
                    return;
                }

                // Middle area: show both.
                scrollTopBtn.style.display = 'inline-flex';
                scrollBottomBtn.style.display = 'inline-flex';
            };

            scrollTopBtn.addEventListener('click', () => {
                const activeScroller = getActiveScroller();
                if (activeScroller === 'main' && mainContainer) {
                    mainContainer.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });

            scrollBottomBtn.addEventListener('click', () => {
                const bottom = getScrollHeight();
                const activeScroller = getActiveScroller();
                if (activeScroller === 'main' && mainContainer) {
                    mainContainer.scrollTo({ top: bottom, behavior: 'smooth' });
                } else {
                    window.scrollTo({ top: bottom, behavior: 'smooth' });
                }
            });

            const onScroll = () => syncVisibility();

            if (mainContainer) {
                mainContainer.addEventListener('scroll', onScroll, { passive: true });
            }
            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', syncVisibility);
            syncVisibility();
        };

        initGlobalScrollButtons();

        const notificationDetails = document.getElementById('notification-details');
        const notificationCountBadge = document.getElementById('notification-count-badge');
        let markReadRequested = false;

        notificationDetails?.addEventListener('toggle', () => {
            if (!notificationDetails.open || markReadRequested) {
                return;
            }

            const unreadCount = Number.parseInt(notificationCountBadge?.textContent ?? '0', 10);
            if (!Number.isFinite(unreadCount) || unreadCount <= 0) {
                return;
            }

            markReadRequested = true;

            fetch("{{ route('notifications.mark-read') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Failed to mark notifications as read.');
                    }

                    if (notificationCountBadge) {
                        notificationCountBadge.textContent = '0';
                    }
                })
                .catch(() => {
                    markReadRequested = false;
                });
        });
    });
</script>
<script>
if ("serviceWorker" in navigator) {
  navigator.serviceWorker.register("{{ asset('sw.js') }}")
    .then(() => console.log("Service Worker registered"));
}
</script>

</body>
</html>