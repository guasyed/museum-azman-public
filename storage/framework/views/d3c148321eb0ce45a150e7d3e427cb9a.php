<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
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
    ?>
	<link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="theme-color" content="<?php echo e($uiTheme === 'dark' ? '#111827' : '#f7f7f6'); ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Museum Azman">
    <link rel="apple-touch-icon" href="<?php echo e(asset('icons/icon-192.png')); ?>">

    <title><?php echo e($title ?? 'Museum Azman'); ?></title>
    <?php
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
    ?>
    <link rel="icon" type="<?php echo e($faviconType); ?>" href="<?php echo e($faviconHref); ?>">
    <link rel="alternate icon" href="<?php echo e($faviconHref); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Lora:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
    <?php
        $host = strtolower((string) request()->getHost());
        $isTunnelHost = str_contains($host, 'pinggy') || str_contains($host, 'ngrok') || str_contains($host, 'loca.lt');
        $isSettingsRoute = request()->routeIs('settings.*');
    ?>

    <?php if($isTunnelHost && $isSettingsRoute): ?>
        <link rel="stylesheet" href="<?php echo e(\Illuminate\Support\Facades\Vite::asset('resources/css/app.css')); ?>">
        <script type="module" src="<?php echo e(\Illuminate\Support\Facades\Vite::asset('resources/js/app.js')); ?>"></script>
    <?php else: ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?>

    <style>
        body.museum-shell {
            --museum-accent: <?php echo e($uiAccent); ?>;
            --museum-font-heading: <?php echo $uiHeadingFontFamily; ?>;
            --museum-font-body: <?php echo $uiBodyFontFamily; ?>;
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
<body class="museum-shell museum-theme-<?php echo e($uiTheme); ?> museum-density-<?php echo e($uiDensity); ?> bg-[#f6f5f4]">

<header class="sticky top-0 z-40 flex items-center justify-between border-b border-zinc-200 bg-[#f7f7f6] p-4 lg:hidden">
    <div class="flex items-center gap-3">
        <?php
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
        ?>
        <?php if($brandLogoUrl): ?>
            <img src="<?php echo e($brandLogoUrl); ?>" alt="logo" class="h-8 w-8 rounded-lg object-cover">
        <?php endif; ?>
        <p class="text-lg font-bold text-zinc-900"><?php echo e($brandTitle); ?></p>
    </div>
    <button id="hamburgerBtn" class="p-2 text-zinc-600">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
</header>

<div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">

    <aside class="hidden lg:flex min-h-screen flex-col border-r border-zinc-200 bg-[#f7f7f6]">
        <div class="border-b border-zinc-200 p-6">
            <div class="flex items-center gap-3">
                <?php if($brandLogoUrl): ?>
                    <img src="<?php echo e($brandLogoUrl); ?>" alt="logo" class="h-12 w-12 rounded-lg object-cover border border-zinc-300">
                <?php endif; ?>
                <p class="museum-brand-title"><?php echo e($brandTitle); ?></p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 p-4 text-sm">
            <a href="<?php echo e(route('dashboard')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
                <span>Dashboard</span>
            </a>

            <a href="<?php echo e(route('artworks.index')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('artworks.*') ? 'active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125 a1.64 1.64 0 0 1 1.668-1.668h1.996 c3.051 0 5.555-2.503 5.555-5.554 C21.965 6.012 17.461 2 12 2z"></path></svg>
                <span>Collection</span>
            </a>

            <a href="<?php echo e(route('movements.index')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('movements.*') ? 'active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
                <span>Movement Tracker</span>
            </a>

            <a href="<?php echo e(route('locations.index')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('locations.*') ? 'active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <span>Locations</span>
            </a>

            <a href="<?php echo e(route('artists.index')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('artists.*') ? 'active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Artists</span>
            </a>

            <a href="<?php echo e(route('reports.index')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="M18 17V9"></path><path d="M13 17V5"></path><path d="M8 17v-3"></path></svg>
                <span>Reports & Analytics</span>
            </a>

            <a href="<?php echo e(route('settings.index')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('settings.*') ? 'active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <span>Settings</span>
            </a>

            <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                    <span>Manage Users</span>
                </a>
                <a href="<?php echo e(route('admin.docs.technical')); ?>" class="museum-nav-item flex items-center gap-3 <?php echo e(request()->routeIs('admin.docs.technical') ? 'active' : ''); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>Technical Docs</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="mt-auto px-4 pb-6 pt-6 space-y-3">
            <p class="px-2 text-xs text-zinc-500">Management System v1.0</p>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto">
        <div class="mx-auto w-full max-w-350 p-6 lg:p-10">
            <?php if(auth()->guard()->check()): ?>
                <?php
                    $pendingRegistrationCount = 0;
                    $pendingRegistrationNotifications = collect();
                    if (
                        auth()->user()->isAdmin()
                        && \Illuminate\Support\Facades\Schema::hasTable('notifications')
                    ) {
                        $pendingRegistrationNotifications = auth()->user()
                            ->unreadNotifications()
                            ->where('type', \App\Notifications\NewUserRegistrationNotification::class)
                            ->latest()
                            ->take(8)
                            ->get();

                        $pendingRegistrationCount = $pendingRegistrationNotifications->count();
                    }
                ?>

                <div class="mb-6 flex items-center justify-end gap-3">
                    <?php if(auth()->user()->isAdmin()): ?>
                        <details class="relative">
                            <summary class="museum-btn-secondary inline-flex list-none items-center gap-2 <?php echo e(request()->routeIs('admin.users.*') ? 'ring-2 ring-zinc-300' : ''); ?> cursor-pointer" title="Pending registration approvals" aria-label="Pending registration approvals">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path><path d="M10 17a2 2 0 0 0 4 0"></path></svg>
                                <span class="inline-flex min-w-6 items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background: var(--museum-accent);"><?php echo e($pendingRegistrationCount); ?></span>
                            </summary>

                            <div class="absolute right-0 z-50 mt-2 w-96 max-w-[90vw] rounded-xl border border-zinc-200 bg-white p-3 shadow-lg">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-zinc-900">Pending Registrations</p>
                                    <a href="<?php echo e(route('admin.users.index')); ?>" class="text-xs font-semibold" style="color: var(--museum-accent);">Manage</a>
                                </div>

                                <?php if($pendingRegistrationNotifications->isEmpty()): ?>
                                    <p class="rounded-lg bg-zinc-50 px-3 py-2 text-xs text-zinc-500">No pending notifications.</p>
                                <?php else: ?>
                                    <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                                        <?php $__currentLoopData = $pendingRegistrationNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e(route('admin.users.index', ['sort' => 'status', 'direction' => 'asc'])); ?>" class="block rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-100">
                                                <p class="font-semibold text-zinc-900"><?php echo e($notification->data['name'] ?? 'User'); ?></p>
                                                <p class="truncate text-zinc-600"><?php echo e($notification->data['email'] ?? 'unknown email'); ?></p>
                                                <?php if(!empty($notification->data['requested_role'])): ?>
                                                    <p>Requested role: <span class="font-semibold"><?php echo e($notification->data['requested_role']); ?></span></p>
                                                <?php endif; ?>
                                                <p class="mt-1 text-zinc-500"><?php echo e($notification->created_at?->diffForHumans()); ?></p>
                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </details>
                    <?php endif; ?>

                    <a href="<?php echo e(route('profile.edit')); ?>" class="museum-btn-secondary inline-flex items-center gap-2">
                        <?php if(auth()->user()->avatar_url): ?>
                            <img src="<?php echo e(auth()->user()->avatar_url); ?>" alt="<?php echo e(auth()->user()->name); ?>" class="h-7 w-7 rounded-full object-cover">
                        <?php else: ?>
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold text-white" style="background: var(--museum-accent);"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 2))); ?></span>
                        <?php endif; ?>
                        <span>Profile</span>
                    </a>

                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="museum-btn-secondary">Logout</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc pl-5">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php echo e($slot); ?>

        </div>
    </main>
</div>

<div
    class="fixed right-4 top-1/2 z-50 flex -translate-y-1/2 flex-col gap-2 lg:right-6"
    style="position:fixed; right:16px; top:50%; transform:translateY(-50%); z-index:2147483000; display:flex; flex-direction:column; gap:8px; pointer-events:auto;"
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
        <a href="<?php echo e(route('dashboard')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">Dashboard</a>
        <a href="<?php echo e(route('artworks.index')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('artworks.*') ? 'active' : ''); ?>">Collection</a>
        <a href="<?php echo e(route('movements.index')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('movements.*') ? 'active' : ''); ?>">Movement Tracker</a>
        <a href="<?php echo e(route('locations.index')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('locations.*') ? 'active' : ''); ?>">Locations</a>
        <a href="<?php echo e(route('artists.index')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('artists.*') ? 'active' : ''); ?>">Artists</a>
        <a href="<?php echo e(route('reports.index')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">Reports & Analytics</a>
        <a href="<?php echo e(route('settings.index')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('settings.*') ? 'active' : ''); ?>">Settings</a>
        <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
            <a href="<?php echo e(route('settings.index', ['tab' => 'users-roles'])); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('settings.*') && request()->string('tab')->toString() === 'users-roles' ? 'active' : ''); ?>">Users &amp; Roles</a>
            <a href="<?php echo e(route('admin.docs.technical')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('admin.docs.technical') ? 'active' : ''); ?>">Technical Docs</a>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">Manage Users</a>
        <?php endif; ?>
        
        <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('profile.edit')); ?>" class="mobile-nav-link <?php echo e(request()->routeIs('profile.*') ? 'active' : ''); ?>">My Profile</a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-8">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full rounded-lg bg-zinc-900 px-4 py-3 text-white font-semibold transition hover:bg-rose-700">Logout</button>
            </form>
        <?php endif; ?>
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
            const useMain = !!mainContainer && (mainContainer.scrollHeight - mainContainer.clientHeight > 1);

            const getScrollTop = () => useMain ? mainContainer.scrollTop : window.scrollY;
            const getScrollHeight = () => useMain
                ? mainContainer.scrollHeight
                : Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
            const getClientHeight = () => useMain ? mainContainer.clientHeight : window.innerHeight;

            const syncVisibility = () => {
                const top = getScrollTop();
                const maxScroll = Math.max(getScrollHeight() - getClientHeight(), 0);
                const edgeThreshold = 24;
                const nearTop = top <= edgeThreshold;
                const nearBottom = top >= Math.max(maxScroll - edgeThreshold, 0);

                scrollTopBtn.classList.toggle('hidden', nearTop);
                scrollTopBtn.classList.toggle('opacity-55', nearTop);

                scrollBottomBtn.classList.toggle('hidden', nearBottom);
                scrollBottomBtn.classList.toggle('opacity-55', nearBottom);
            };

            scrollTopBtn.addEventListener('click', () => {
                if (useMain) {
                    mainContainer.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });

            scrollBottomBtn.addEventListener('click', () => {
                const bottom = getScrollHeight();
                if (useMain) {
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
    });
</script>
<script>
if ("serviceWorker" in navigator) {
  navigator.serviceWorker.register("<?php echo e(asset('sw.js')); ?>")
    .then(() => console.log("Service Worker registered"));
}
</script>

</body>
</html><?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/layouts/app.blade.php ENDPATH**/ ?>