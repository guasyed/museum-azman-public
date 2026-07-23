@php
    $publicPage = $publicPage ?? 'home';
    $artworks = collect($homeArtworks ?? [])->values();
    $fallbackImage = asset('museum-azman.jpeg');
    $imageFor = function (int $index) use ($artworks, $fallbackImage) {
        $artwork = $artworks->get($index);

        return $artwork?->primary_image_url ?: $artwork?->source_image_url ?: $fallbackImage;
    };
    $titleFor = fn (int $index, string $fallback) => $artworks->get($index)?->title ?: $fallback;
    $artistFor = fn (int $index, string $fallback = 'Museum Azman') => $artworks->get($index)?->artist?->name ?: $fallback;
    $countryFor = fn (int $index, string $fallback = 'Contemporary Collection') => $artworks->get($index)?->artist?->country ?: $fallback;
    $yearFor = fn (int $index, string $fallback = '2026') => $artworks->get($index)?->year ?: $fallback;
    $mediumFor = fn (int $index, string $fallback = 'Mixed media') => $artworks->get($index)?->medium ?: $fallback;
    $routes = [
        'home' => route('home', [], false),
        'about' => route('public.about', [], false),
        'events' => route('public.events', [], false),
        'artists' => route('public.artists', [], false),
        'collection' => route('public.collection', [], false),
        'visit' => route('public.visit', [], false),
        'contact' => route('public.contact', [], false),
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Museum Azman | {{ $publicPage === 'home' ? 'Contemporary Art' : ucfirst($publicPage) }}</title>
    <meta name="description" content="Museum Azman is a private contemporary art museum featuring artists from the Americas to Southeast Asia.">
    <style>
        :root {
            --page: #050505;
            --panel: #0b0b0a;
            --panel-2: #111110;
            --field: #151514;
            --line: rgba(255, 255, 255, 0.1);
            --muted: rgba(255, 255, 255, 0.62);
            --soft: rgba(255, 255, 255, 0.78);
            --text: #f4f0e8;
            --gold: #c8a85d;
            --gold-soft: #e7d295;
            --public-font-sans: Arial, Helvetica, sans-serif;
            --public-font-serif: Georgia, "Times New Roman", serif;
            --public-size-nav: 14px;
            --public-size-body: 18px;
            --public-size-section-title: clamp(2rem, 2.6vw, 2.5rem);
            --public-size-label: 14px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            background: var(--page);
        }

        body {
            margin: 0;
            background: var(--page);
            color: var(--text);
            font-family: var(--public-font-sans);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img,
        video {
            display: block;
            max-width: 100%;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            font-family: var(--public-font-serif);
            font-weight: 400;
        }

        p {
            margin: 0;
        }

        .site-header {
            position: fixed;
            z-index: 20;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
            padding: 18px clamp(34px, 3vw, 44px);
            background: #000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            min-width: 190px;
        }

        .brand-logo {
            display: block;
            width: 190px;
            height: auto;
            object-fit: contain;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: clamp(18px, 2.2vw, 30px);
            color: rgba(255, 255, 255, 0.58);
            font-size: var(--public-size-nav);
            font-weight: 500;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .nav a {
            transition: color 160ms ease;
        }

        .nav a:hover,
        .nav a.active {
            color: var(--gold-soft);
        }

        .hero {
            position: relative;
            min-height: 620px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-bottom: 1px solid var(--line);
        }

        .hero video,
        .image-hero img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.68;
        }

        .image-hero img {
            cursor: zoom-in;
        }

        .hero::after,
        .image-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.52);
        }

        .hero-content,
        .image-hero-content {
            position: relative;
            z-index: 1;
            width: min(1120px, calc(100% - 44px));
            padding-top: 70px;
            text-align: center;
        }

        .hero h1 {
            font-size: clamp(4rem, 7.8vw, 6.5rem);
            line-height: 0.92;
            color: rgba(255, 255, 255, 0.72);
        }

        .image-hero {
            position: relative;
            min-height: 360px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-bottom: 1px solid var(--line);
        }

        .image-hero h1,
        .page-title {
            font-size: clamp(3.6rem, 7.2vw, 6.4rem);
            line-height: 0.95;
        }

        .image-hero p,
        .page-copy {
            margin-top: 18px;
            color: var(--muted);
            font-size: clamp(1rem, 1.7vw, 1.25rem);
        }

        .page-intro {
            padding: 160px clamp(28px, 4vw, 76px) 70px;
            border-bottom: 1px solid var(--line);
            background: #070707;
        }

        .page-intro-inner,
        .section-inner {
            width: min(1380px, 100%);
            margin: 0 auto;
        }

        .page-copy {
            max-width: 760px;
        }

        .section {
            padding: 58px clamp(34px, 3vw, 54px) 62px;
            border-bottom: 1px solid var(--line);
            background: #070707;
        }

        .section.alt {
            background: #0d0d0c;
        }

        .section.narrow {
            padding-left: 22px;
            padding-right: 22px;
        }

        .section-head {
            width: 100%;
            max-width: none;
            margin: 0 auto 42px;
        }

        .section-head.center {
            text-align: center;
        }

        .section-head h2,
        .text-panel h2 {
            font-size: var(--public-size-section-title);
            line-height: 1;
        }

        .section-head p {
            max-width: 620px;
            margin-top: 12px;
            color: var(--muted);
            font-size: 14px;
        }

        .section-head.center p {
            margin-left: auto;
            margin-right: auto;
        }

        .grid {
            width: 100%;
            max-width: none;
            margin: 0 auto;
            display: grid;
            gap: clamp(28px, 3vw, 38px);
        }

        .grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid.four {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .card {
            min-width: 0;
            overflow: clip;
            transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1);
        }

        .card img {
            width: 100%;
            aspect-ratio: 4 / 5;
            object-fit: cover;
            border-radius: 2px;
            background: #151515;
            cursor: zoom-in;
            transition: transform 700ms cubic-bezier(0.22, 1, 0.36, 1), filter 500ms ease, opacity 500ms ease;
            will-change: transform;
        }

        .card:hover {
            transform: translateY(-8px);
        }

        .card:hover img {
            transform: scale(1.045);
            filter: brightness(1.08) contrast(1.03);
        }

        .artist-card:hover img {
            filter: grayscale(0.25) brightness(1.08) contrast(1.03);
        }

        .square-card img,
        .collection-card img {
            aspect-ratio: 1 / 1;
        }

        .event-placeholder {
            display: grid;
            width: 100%;
            aspect-ratio: 1 / 1;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: #151515;
            color: rgba(255, 255, 255, 0.46);
            font-family: var(--public-font-serif);
            font-size: clamp(1.35rem, 2.2vw, 2rem);
        }

        .wide-card img {
            aspect-ratio: 16 / 11;
        }

        .artist-card img {
            filter: grayscale(1);
        }

        .card h3 {
            margin-top: 16px;
            color: #f5f1e8;
            font-family: var(--public-font-serif);
            font-size: 18px;
            font-weight: 400;
            line-height: 1.15;
        }

        .home-card h3 {
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            font-size: 15px;
            font-weight: 700;
        }

        .card p {
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
        }

        .card small {
            display: block;
            margin-top: 4px;
            color: var(--gold-soft);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .section-link {
            display: flex;
            justify-content: center;
            margin: 42px auto 0;
            color: var(--gold-soft);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .public-page-home .section {
            padding: 58px clamp(34px, 3vw, 54px) 62px;
        }

        .public-page-home .section-head {
            width: 100%;
            max-width: none;
            margin-bottom: 36px;
        }

        .public-page-home .grid {
            width: 100%;
            max-width: none;
            gap: clamp(38px, 3.2vw, 56px);
        }

        .public-page-home .section-head h2 {
            font-size: clamp(1.75rem, 2.3vw, 2.2rem);
        }

        .public-page-home .section-head p {
            margin-top: 6px;
            font-size: 12px;
        }

        .public-page-home .home-card h3 {
            margin-top: 12px;
            font-size: 12px;
        }

        .public-page-home .card p {
            font-size: 10px;
        }

        .public-page-home .card small {
            font-size: 8px;
        }

        .public-page-home .section-link {
            margin-top: 38px;
            font-size: 9px;
        }

        .experience {
            position: relative;
            min-height: 360px;
            display: grid;
            place-items: center;
            overflow: hidden;
            background-image: linear-gradient(rgba(0, 0, 0, 0.54), rgba(0, 0, 0, 0.58)), url("{{ $imageFor(10) }}");
            background-size: cover;
            background-position: center;
            text-align: center;
        }

        .experience-inner,
        .vision-inner,
        .center-copy {
            width: min(820px, 100%);
            margin: 0 auto;
            text-align: center;
        }

        .experience h2,
        .vision h2,
        .center-copy h2 {
            font-size: var(--public-size-section-title);
            line-height: 1.05;
        }

        .experience p,
        .vision p,
        .center-copy p,
        .text-panel p {
            margin-top: 16px;
            color: rgba(255, 255, 255, 0.68);
            font-family: var(--public-font-sans);
            font-size: var(--public-size-body);
            line-height: 1.55;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 28px;
            min-height: 46px;
            padding: 0 24px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .button.gold,
        .public-form button {
            background: var(--gold);
            border-color: var(--gold);
            color: #070707;
        }

        .vision {
            padding: clamp(70px, 10vw, 128px) 22px;
            text-align: center;
            background: #080808;
            border-bottom: 1px solid var(--line);
        }

        .vision .note {
            color: var(--gold-soft);
            font-weight: 700;
        }

        .program-grid,
        .value-grid,
        .visit-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 44px;
            width: min(820px, 100%);
            margin: 44px auto 0;
            text-align: center;
        }

        .program-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .program-grid h3,
        .value-grid h3,
        .visit-stats h3,
        .form-section-title {
            font-family: var(--public-font-serif);
            font-size: 18px;
            font-weight: 400;
        }

        .program-grid p,
        .value-grid p,
        .visit-stats p {
            margin-top: 8px;
            color: var(--muted);
            font-family: var(--public-font-sans);
            font-size: var(--public-size-label);
        }

        .text-panel {
            width: min(850px, 100%);
            margin: 0 auto;
        }

        .space-grid,
        .contact-grid {
            width: min(1380px, 100%);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: clamp(40px, 8vw, 120px);
            align-items: start;
        }

        .space-grid img {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            cursor: zoom-in;
        }

        .public-form {
            display: grid;
            gap: 22px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            display: block;
            margin-bottom: 9px;
            color: rgba(255, 255, 255, 0.8);
            font-family: var(--public-font-sans);
            font-size: var(--public-size-label);
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            min-height: 56px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0;
            background: var(--field);
            color: #fff;
            padding: 0 18px;
            font: inherit;
        }

        .field textarea {
            min-height: 140px;
            padding-top: 16px;
            resize: vertical;
        }

        .public-form button {
            width: 100%;
            min-height: 60px;
            border: 0;
            border-radius: 0;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            cursor: pointer;
        }

        .checkbox-list {
            display: grid;
            gap: 12px;
        }

        .checkbox-list label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: var(--muted);
            font-size: 13px;
        }

        .checkbox-list input {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            accent-color: var(--gold);
        }

        .form-response {
            min-height: 22px;
            color: var(--gold-soft);
            font-size: 13px;
            text-align: center;
        }

        .contact-list {
            display: grid;
            gap: 26px;
            margin-top: 44px;
        }

        .contact-item {
            display: grid;
            grid-template-columns: 54px 1fr;
            gap: 18px;
            align-items: start;
        }

        .contact-icon {
            display: grid;
            place-items: center;
            width: 54px;
            height: 54px;
            border: 1px solid var(--line);
            color: var(--gold-soft);
            font-size: 24px;
        }

        .contact-item h3 {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .contact-item p {
            margin-top: 8px;
            color: var(--muted);
            font-size: 17px;
        }

        .footer {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) repeat(3, minmax(130px, 0.55fr));
            gap: 36px;
            padding: 64px clamp(28px, 4vw, 76px) 36px;
            background: #000;
        }

        .footer p,
        .footer a {
            color: var(--muted);
            font-size: 12px;
        }

        .footer h3 {
            margin-bottom: 12px;
            color: #fff;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 13px;
            font-weight: 400;
            text-transform: uppercase;
        }

        .footer-links {
            display: grid;
            gap: 8px;
        }

        .socials {
            display: flex;
            gap: 10px;
        }

        .socials a {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border: 1px solid var(--line);
        }

        .copyright {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding-top: 30px;
            border-top: 1px solid var(--line);
        }

        .landing-lightbox {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            padding: clamp(16px, 3vw, 42px);
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
        }

        .landing-lightbox.is-open {
            display: flex;
        }

        .landing-lightbox img {
            max-width: min(100%, 1180px);
            max-height: calc(100vh - 110px);
            object-fit: contain;
            border-radius: 2px;
        }

        .landing-lightbox button {
            position: absolute;
            top: 20px;
            right: 22px;
            width: 44px;
            height: 44px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-size: 30px;
            line-height: 1;
            cursor: pointer;
        }

        .landing-lightbox p {
            position: absolute;
            left: clamp(16px, 3vw, 42px);
            bottom: clamp(18px, 3vw, 34px);
            max-width: min(760px, calc(100vw - 120px));
            color: rgba(244, 240, 232, 0.82);
            font-size: 0.9rem;
        }

        .site-header {
            animation: public-header-enter 850ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .hero-content,
        .image-hero-content,
        .page-intro-inner {
            animation: public-intro-enter 900ms 100ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .hero video,
        .image-hero img {
            animation: public-hero-image-enter 1400ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .reveal-item {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 750ms ease, transform 850ms cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--reveal-delay, 0ms);
        }

        .reveal-item.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .card.reveal-item.is-visible:hover {
            transform: translateY(-8px);
        }

        .nav a {
            position: relative;
        }

        .nav a::after {
            content: "";
            position: absolute;
            right: 0;
            bottom: -7px;
            left: 0;
            height: 1px;
            background: var(--gold-soft);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 300ms cubic-bezier(0.22, 1, 0.36, 1);
        }

        .nav a:hover::after,
        .nav a.active::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .button,
        .public-form button {
            transition: transform 250ms ease, filter 250ms ease, box-shadow 250ms ease;
        }

        .button:hover,
        .public-form button:hover {
            transform: translateY(-2px);
            filter: brightness(1.08);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.28);
        }

        @keyframes public-header-enter {
            from { opacity: 0; transform: translateY(-18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes public-intro-enter {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes public-hero-image-enter {
            from { opacity: 0; transform: scale(1.06); }
            to { opacity: 0.68; transform: scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .reveal-item {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 980px) {
            .site-header {
                position: absolute;
                min-height: 76px;
            }

            .hero {
                min-height: 520px;
            }

            .nav {
                display: none;
            }

            .page-intro {
                padding-top: 124px;
            }

            .grid.two,
            .grid.three,
            .grid.four,
            .footer,
            .space-grid,
            .contact-grid,
            .form-grid,
            .program-grid,
            .value-grid,
            .visit-stats {
                grid-template-columns: 1fr;
            }

            .grid.four {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .copyright {
                display: grid;
            }
        }

        @media (max-width: 620px) {
            .hero h1 {
                font-size: clamp(3.3rem, 18vw, 5.5rem);
            }

            .grid.four {
                grid-template-columns: 1fr;
            }

            .section,
            .page-intro {
                padding-left: 18px;
                padding-right: 18px;
            }
        }
    </style>
</head>
<body class="public-page public-page-{{ $publicPage }}">
    <header class="site-header">
        <a class="brand" href="{{ $routes['home'] }}" aria-label="Museum Azman home">
            <img class="brand-logo" src="{{ asset('media/museum-azman-logo.jpeg') }}" alt="Museum Azman">
        </a>
        <nav class="nav" aria-label="Main navigation">
            @foreach(['about' => 'About', 'events' => 'Events', 'artists' => 'Artists', 'collection' => 'Collection', 'visit' => 'Visit', 'contact' => 'Contact'] as $key => $label)
                <a class="{{ $publicPage === $key ? 'active' : '' }}" href="{{ $routes[$key] }}">{{ $label }}</a>
            @endforeach
        </nav>
    </header>

    <main>
        @if($publicPage === 'home')
            <section class="hero" aria-label="Museum Azman">
                <video autoplay muted loop playsinline poster="{{ $fallbackImage }}">
                    <source src="{{ asset('media/museum-azman-home-video.mp4') }}" type="video/mp4">
                </video>
            </section>

            <section class="section">
                <div class="section-head">
                    <h2>Featured Events</h2>
                    <p>Exhibitions, talks, and special programming.</p>
                </div>
                <div class="grid three">
                    @foreach([0, 1, 2] as $index)
                        <article class="card home-card">
                            <img src="{{ $imageFor($index) }}" alt="{{ ['Chromatic Dialogues', 'Silent Forms', 'Urban Narratives'][$index] }}" loading="lazy">
                            <h3>{{ ['Chromatic Dialogues', 'Silent Forms', 'Urban Narratives'][$index] }}</h3>
                            <p>{{ $artistFor($index, 'Museum Azman') }}</p>
                            <small>{{ ['March - May 2026', 'April - June 2026', 'May - July 2026'][$index] }}</small>
                        </article>
                    @endforeach
                </div>
                <a class="section-link" href="{{ $routes['events'] }}">View all events -></a>
            </section>

            <section class="section alt">
                <div class="section-head">
                    <h2>Featured Artists</h2>
                    <p>Voices shaping contemporary art across continents.</p>
                </div>
                <div class="grid four">
                    @foreach([3, 4, 5, 6] as $index)
                        <article class="card home-card artist-card">
                            <img src="{{ $imageFor($index) }}" alt="{{ $artistFor($index, 'Featured artist') }}" loading="lazy">
                            <h3>{{ $artistFor($index, 'Featured Artist') }}</h3>
                            <small>{{ $countryFor($index) }}</small>
                        </article>
                    @endforeach
                </div>
                <a class="section-link" href="{{ $routes['artists'] }}">Discover all artists -></a>
            </section>

            <section class="section">
                <div class="section-head">
                    <h2>Selected Works</h2>
                    <p>Highlights from our permanent collection.</p>
                </div>
                <div class="grid three">
                    @foreach([7, 8, 9] as $index)
                        <article class="card home-card collection-card">
                            <img src="{{ $imageFor($index) }}" alt="{{ $titleFor($index, 'Selected artwork') }}" loading="lazy">
                            <h3>{{ $titleFor($index, 'Selected Work') }}</h3>
                            <p>{{ $artistFor($index) }}, {{ $yearFor($index, '2024') }}</p>
                            <small>{{ $mediumFor($index) }}</small>
                        </article>
                    @endforeach
                </div>
                <a class="section-link" href="{{ $routes['collection'] }}">Explore collection -></a>
            </section>

            <section class="experience">
                <div class="experience-inner">
                    <h2>Experience Art Intimately</h2>
                    <p>Museum Azman offers exclusive private viewings for collectors, curators, and art enthusiasts. Engage with our collection in a contemplative environment designed for deep appreciation.</p>
                    <a class="button" href="{{ $routes['visit'] }}">Request your visit -></a>
                </div>
            </section>

            <section class="vision">
                <div class="vision-inner">
                    <h2>Our Vision</h2>
                    <p>Museum Azman bridges cultural perspectives across the Americas and Southeast Asia, presenting contemporary art that challenges, inspires, and connects diverse voices.</p>
                    <p>Currently operating as a private museum, we cultivate an intimate environment for serious engagement with art. Our future vision includes opening to the public while maintaining our commitment to thoughtful, immersive experiences.</p>
                    <p class="note">Join our community to receive updates on our journey toward public opening.</p>
                    <a class="button gold" href="{{ $routes['visit'] }}">Request private viewing -></a>
                </div>
            </section>
        @elseif($publicPage === 'events')
            <section class="page-intro">
                <div class="page-intro-inner">
                    <h1 class="page-title">{{ $eventContent['public_events_page_title'] }}</h1>
                    <p class="page-copy">{{ $eventContent['public_events_page_description'] }}</p>
                </div>
            </section>

            @foreach([
                'currently_active' => ['label' => 'Currently Active', 'alternate' => true],
                'upcoming' => ['label' => 'Upcoming', 'alternate' => false],
                'archive' => ['label' => 'Archive', 'alternate' => true],
            ] as $eventSection => $sectionConfig)
                @php $sectionEvents = collect($publicEvents->get($eventSection, []))->take(3)->values(); @endphp
                <section class="section {{ $sectionConfig['alternate'] ? 'alt' : '' }}">
                    <div class="section-head"><h2>{{ $sectionConfig['label'] }}</h2></div>
                    <div class="grid three">
                        @foreach(range(0, 2) as $slot)
                            @php $event = $sectionEvents->get($slot); @endphp
                            <article class="card square-card">
                                @if($event?->image_url)
                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}" loading="lazy">
                                @else
                                    <div class="event-placeholder">Coming Soon</div>
                                @endif
                                <h3>{{ $event?->title ?: 'Coming Soon' }}</h3>
                                <p>{{ $event?->event_type ?: 'Event announcement' }}</p>
                                <small>{{ $event?->schedule ?: 'Details to be announced' }}</small>
                                @if($event?->description)
                                    <p>{{ $event->description }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section class="section">
                <div class="center-copy">
                    <h2>{{ $eventContent['public_events_programming_title'] }}</h2>
                    <div class="program-grid">
                        @foreach(range(1, 4) as $number)
                            <div>
                                <h3>{{ $eventContent['public_events_program_'.$number.'_title'] }}</h3>
                                <p>{{ $eventContent['public_events_program_'.$number.'_description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @elseif($publicPage === 'artists')
            <section class="page-intro">
                <div class="page-intro-inner">
                    <h1 class="page-title">{{ $artistContent['public_artists_page_title'] }}</h1>
                    <p class="page-copy">{{ $artistContent['public_artists_page_description'] }}</p>
                </div>
            </section>

            <section class="section">
                <div class="grid four">
                    @if($artistsCmsConfigured)
                        @forelse($publicArtistProfiles as $profile)
                            @php
                                $artist = $profile->artist;
                                $profileImage = $profile->image_url ?: $artist?->artworks?->first(fn ($artwork) => filled($artwork->primary_image_url))?->primary_image_url;
                            @endphp
                            <article class="card artist-card">
                                @if($profileImage)
                                    <img src="{{ $profileImage }}" alt="{{ $artist?->name ?? 'Artist' }}" loading="lazy">
                                @else
                                    <div class="event-placeholder" role="img" aria-label="Portrait coming soon"><span>Portrait<br>Coming Soon</span></div>
                                @endif
                                <h3>{{ $artist?->name ?? 'Artist' }}</h3>
                                <small>{{ $artist?->country ?: 'International' }}</small>
                                @if($profile->biography)<p>{{ $profile->biography }}</p>@endif
                            </article>
                        @empty
                            <p class="page-copy">Artist profiles coming soon.</p>
                        @endforelse
                    @else
                        @foreach([3, 4, 5, 6, 7, 8, 9, 10] as $loopIndex => $index)
                            <article class="card artist-card">
                                <img src="{{ $imageFor($index) }}" alt="{{ $artistFor($index, 'Artist') }}" loading="lazy">
                                <h3>{{ $artistFor($index, ['Aurelius Wendleken', 'Fons Heijnsbroek', 'Kseniya Lapteva', 'Margarita Shtyfura', 'Chen Wei', 'Anh Vy', 'Sofia Ramirez', 'Elena Torres'][$loopIndex]) }}</h3>
                                <small>{{ $countryFor($index, ['Germany', 'Netherlands', 'Russia', 'Ukraine', 'China', 'Vietnam', 'Mexico', 'Brazil'][$loopIndex]) }}</small>
                            </article>
                        @endforeach
                    @endif
                </div>
            </section>

            <section class="section alt">
                <div class="center-copy">
                    <h2>{{ $artistContent['public_artists_collaboration_title'] }}</h2>
                    <p>{{ $artistContent['public_artists_collaboration_description'] }}</p>
                </div>
            </section>
        @elseif($publicPage === 'collection')
            <section class="page-intro">
                <div class="page-intro-inner">
                    <h1 class="page-title">{{ $collectionContent['public_collection_page_title'] }}</h1>
                    <p class="page-copy">{{ $collectionContent['public_collection_page_description'] }}</p>
                </div>
            </section>

            <section class="section">
                <div class="grid three">
                    @if($collectionCmsConfigured)
                        @forelse($publicCollectionItems as $item)
                            @php $artwork = $item->artwork; @endphp
                            <article class="card collection-card">
                                @if($artwork?->primary_image_url)
                                    <img src="{{ $artwork->primary_image_url }}" alt="{{ $artwork->title ?: 'Collection artwork' }}" loading="lazy">
                                @else
                                    <div class="event-placeholder" role="img" aria-label="Artwork image coming soon">Image Coming Soon</div>
                                @endif
                                <h3>{{ $artwork?->title ?: 'Untitled' }}</h3>
                                <p>{{ $artwork?->artist?->name ?: 'Museum Azman' }}{{ $artwork?->year ? ', '.$artwork->year : '' }}</p>
                                <small>{{ $artwork?->medium ?: 'Medium not specified' }}</small>
                            </article>
                        @empty
                            <p class="page-copy">Collection highlights coming soon.</p>
                        @endforelse
                    @else
                        @foreach(range(0, 8) as $index)
                            <article class="card collection-card">
                                <img src="{{ $imageFor($index) }}" alt="{{ $titleFor($index, 'Collection artwork') }}" loading="lazy">
                                <h3>{{ $titleFor($index, ['Chromatic Resonance', 'Abstract Composition III', 'Geometric Harmony', 'Pink Dreams', 'Expressive Forms', 'Distant Shores', 'Vivid Emotions', 'Fluid Transitions', 'Color Study'][$index]) }}</h3>
                                <p>{{ $artistFor($index) }}, {{ $yearFor($index, '2024') }}</p>
                                <small>{{ $mediumFor($index, 'Oil on canvas') }}</small>
                            </article>
                        @endforeach
                    @endif
                </div>
            </section>

            <section class="section alt">
                <div class="text-panel">
                    <h2>{{ $collectionContent['public_collection_philosophy_title'] }}</h2>
                    <p>{{ $collectionContent['public_collection_philosophy_paragraph_1'] }}</p>
                    <p>{{ $collectionContent['public_collection_philosophy_paragraph_2'] }}</p>
                    <p>{{ $collectionContent['public_collection_philosophy_paragraph_3'] }}</p>
                </div>
            </section>
        @elseif($publicPage === 'visit')
            <section class="image-hero">
                <img src="{{ $imageFor(10) }}" alt="Museum Azman private viewing" loading="eager">
                <div class="image-hero-content">
                    <h1>Request a Visit</h1>
                    <p>Experience our collection through a private viewing</p>
                </div>
            </section>

            <section class="section alt">
                <div class="center-copy">
                    <h2>Private Viewings</h2>
                    <p>Museum Azman offers exclusive private viewings for collectors, curators, artists, and art enthusiasts. Complete the registration form below to request your visit. All requests are reviewed and confirmed by our team.</p>
                </div>
                <div class="visit-stats">
                    <div><h3>Duration</h3><p>60-90 minutes</p></div>
                    <div><h3>Availability</h3><p>Tuesday - Saturday</p></div>
                    <div><h3>Group Size</h3><p>Up to 6 guests</p></div>
                </div>
            </section>

            <section class="section narrow">
                <div class="text-panel">
                    <h2>Visitor Registration</h2>
                    <form class="public-form" method="POST" action="{{ route('public.visit.store', [], false) }}">
                        @csrf
                        @if(session('visit_success'))
                            <p class="form-response" role="status">{{ session('visit_success') }}</p>
                        @endif
                        @if($errors->any())
                            <p class="form-response" role="alert">{{ $errors->first() }}</p>
                        @endif
                        <p class="form-section-title">Personal Information</p>
                        <div class="form-grid">
                            <div class="field"><label for="visit-name">Full Name *</label><input id="visit-name" name="name" value="{{ old('name') }}" maxlength="255" required></div>
                            <div class="field"><label for="visit-phone">Phone Number *</label><input id="visit-phone" name="phone" value="{{ old('phone') }}" maxlength="50" required></div>
                            <div class="field full"><label for="visit-email">Email Address *</label><input id="visit-email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required></div>
                            <div class="field"><label for="visit-occupation">Occupation *</label><input id="visit-occupation" name="occupation" value="{{ old('occupation') }}" maxlength="255" required></div>
                            <div class="field"><label for="visit-company">Company / Organisation *</label><input id="visit-company" name="company" value="{{ old('company') }}" maxlength="255" required></div>
                            <div class="field full"><label for="visit-city">City / Country *</label><input id="visit-city" name="city" value="{{ old('city') }}" maxlength="255" required></div>
                            <div class="field full"><label for="visit-social">Social Media Profile (Optional)</label><input id="visit-social" name="social" value="{{ old('social') }}" maxlength="255" placeholder="Instagram, LinkedIn, etc."></div>
                        </div>
                        <p class="form-section-title">Visit Details</p>
                        <div class="form-grid">
                            <div class="field full"><label for="visit-purpose">Purpose of Visit *</label><select id="visit-purpose" name="purpose" required><option value="">Select purpose</option>@foreach(['Collector viewing', 'Curatorial research', 'Artist visit', 'Private tour'] as $option)<option @selected(old('purpose') === $option)>{{ $option }}</option>@endforeach</select></div>
                            <div class="field full"><label for="visit-category">Interest Category *</label><select id="visit-category" name="category" required><option value="">Select category</option>@foreach(['Contemporary painting', 'Southeast Asian art', 'Acquisition inquiry', 'General viewing'] as $option)<option @selected(old('category') === $option)>{{ $option }}</option>@endforeach</select></div>
                            <div class="field"><label for="visit-date">Preferred Visit Date *</label><input id="visit-date" name="date" type="date" min="{{ now()->toDateString() }}" value="{{ old('date') }}" required></div>
                            <div class="field"><label for="visit-guests">Number of Guests *</label><input id="visit-guests" name="guests" type="number" min="1" max="6" value="{{ old('guests', 1) }}" required></div>
                            <div class="field full"><label for="visit-source">How Did You Hear About Us? *</label><select id="visit-source" name="source" required><option value="">Select source</option>@foreach(['Collector referral', 'Artist referral', 'Social media', 'Press or publication'] as $option)<option @selected(old('source') === $option)>{{ $option }}</option>@endforeach</select></div>
                            <div class="field full"><label for="visit-message">Message / Special Requests</label><textarea id="visit-message" name="message" maxlength="5000" placeholder="Share any specific interests, questions, or accessibility requirements">{{ old('message') }}</textarea></div>
                        </div>
                        <p class="form-section-title">Preferences</p>
                        <div class="checkbox-list">
                            <label><input type="checkbox" name="preference[]" value="outside-hours" @checked(in_array('outside-hours', old('preference', []), true))> Request exclusive private viewing outside regular hours</label>
                            <label><input type="checkbox" name="preference[]" value="curator" @checked(in_array('curator', old('preference', []), true))> Request guided tour with curator</label>
                            <label><input type="checkbox" name="preference[]" value="events" @checked(in_array('events', old('preference', []), true))> Receive invitations to exclusive events, talks, and special programming</label>
                            <label><input type="checkbox" name="preference[]" value="updates" @checked(in_array('updates', old('preference', []), true))> Receive updates about future public opening plans</label>
                            <label><input type="checkbox" name="consent" value="1" required> I agree to the privacy policy and consent to Museum Azman storing and processing my information for coordinating my visit. *</label>
                        </div>
                        <button type="submit">Submit Request</button>
                    </form>
                </div>
            </section>
        @elseif($publicPage === 'contact')
            <section class="page-intro">
                <div class="page-intro-inner">
                    <h1 class="page-title">Contact</h1>
                    <p class="page-copy">We welcome inquiries from collectors, curators, artists, and art enthusiasts.</p>
                </div>
            </section>

            <section class="section">
                <div class="contact-grid">
                    <div>
                        <h2>Get in Touch</h2>
                        <p class="page-copy">For private viewing requests, please use our visitor registration form. For all other inquiries, reach out through the contact details below.</p>
                        <div class="contact-list">
                            <div class="contact-item"><span class="contact-icon">@</span><div><h3>Email</h3><p>info@museumazman.com</p></div></div>
                            <!--<div class="contact-item"><span class="contact-icon">T</span><div><h3>Phone</h3><p>+1 (234) 567-8900</p></div></div>-->
                            <div class="contact-item"><span class="contact-icon">L</span><div><h3>Location</h3><p>Museum Azman<br>By Invitation Only<br>Location disclosed upon registration</p></div></div>
                        </div>
                        <div class="text-panel" style="margin: 48px 0 0; border-top: 1px solid var(--line); padding-top: 34px;">
                            <h3>Visiting Hours</h3>
                            <p>By appointment only<br>Tuesday - Saturday<br>10:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                    <form class="public-form" method="POST" action="{{ route('public.contact.store', [], false) }}">
                        @csrf
                        @if(session('contact_success'))
                            <p class="form-response" role="status">{{ session('contact_success') }}</p>
                        @endif
                        @if($errors->any())
                            <p class="form-response" role="alert">{{ $errors->first() }}</p>
                        @endif
                        <div class="field"><label for="contact-name">Name *</label><input id="contact-name" name="name" value="{{ old('name') }}" maxlength="255" required></div>
                        <div class="field"><label for="contact-email">Email *</label><input id="contact-email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required></div>
                        <div class="field"><label for="contact-subject">Subject</label><input id="contact-subject" name="subject" value="{{ old('subject') }}" maxlength="255"></div>
                        <div class="field"><label for="contact-message">Message *</label><textarea id="contact-message" name="message" maxlength="5000" required>{{ old('message') }}</textarea></div>
                        <button type="submit">Send Message</button>
                    </form>
                </div>
            </section>
        @elseif($publicPage === 'about')
            <section class="image-hero">
                <img src="{{ $aboutHeroImageUrl ?: $imageFor(11) }}" alt="{{ $aboutContent['public_about_hero_title'] }}" loading="eager">
                <div class="image-hero-content">
                    <h1>{{ $aboutContent['public_about_hero_title'] }}</h1>
                    <p>{{ $aboutContent['public_about_hero_subtitle'] }}</p>
                </div>
            </section>

            <section class="section">
                <div class="text-panel">
                    <h2>{{ $aboutContent['public_about_mission_title'] }}</h2>
                    @foreach(range(1, 3) as $number)<p>{{ $aboutContent['public_about_mission_paragraph_'.$number] }}</p>@endforeach
                </div>
            </section>

            <section class="section alt">
                <div class="text-panel">
                    <h2>{{ $aboutContent['public_about_forward_title'] }}</h2>
                    <p>{{ $aboutContent['public_about_forward_paragraph_1'] }}</p>
                    <p>{{ $aboutContent['public_about_forward_paragraph_2'] }}</p>
                    <p style="color: var(--gold-soft); font-weight: 700;">{{ $aboutContent['public_about_forward_paragraph_3'] }}</p>
                </div>
            </section>

            <section class="section">
                <div class="center-copy">
                    <h2>{{ $aboutContent['public_about_values_title'] }}</h2>
                    <div class="value-grid">
                        @foreach(range(1, 3) as $number)<div><h3>{{ $aboutContent['public_about_value_'.$number.'_title'] }}</h3><p>{{ $aboutContent['public_about_value_'.$number.'_description'] }}</p></div>@endforeach
                    </div>
                </div>
            </section>

            <section class="section alt">
                <div class="space-grid">
                    <div>
                        <h2>{{ $aboutContent['public_about_space_title'] }}</h2>
                        <p class="page-copy">{{ $aboutContent['public_about_space_paragraph_1'] }}</p>
                        <p class="page-copy">{{ $aboutContent['public_about_space_paragraph_2'] }}</p>
                    </div>
                    <img src="{{ $aboutSpaceImageUrl ?: $imageFor(12) }}" alt="{{ $aboutContent['public_about_space_title'] }}" loading="lazy">
                </div>
            </section>
        @endif
    </main>

    <footer class="footer">
        <div>
            <a class="brand" href="{{ $routes['home'] }}" aria-label="Museum Azman home">
                <img class="brand-logo" src="{{ asset('media/museum-azman-logo.jpeg') }}" alt="Museum Azman">
            </a>
            <p>A private contemporary art museum featuring artists from the Americas to Southeast Asia.</p>
        </div>
        <div>
            <h3>Navigate</h3>
            <div class="footer-links">
                <a href="{{ $routes['about'] }}">About</a>
                <a href="{{ $routes['events'] }}">Events</a>
                <a href="{{ $routes['artists'] }}">Artists</a>
                <a href="{{ $routes['collection'] }}">Collection</a>
            </div>
        </div>
        <div>
            <h3>Visit</h3>
            <div class="footer-links">
                <a href="{{ $routes['visit'] }}">Request Visit</a>
                <a href="{{ $routes['contact'] }}">Contact</a>
                @auth
                    <a href="{{ route('dashboard', [], false) }}">Dashboard</a>
                @else
                    <a href="{{ route('login', [], false) }}">Login</a>
                @endauth
            </div>
        </div>
        <div>
            <h3>Connect</h3>
            <div class="socials">
                <a href="{{ $routes['contact'] }}" aria-label="Instagram">I</a>
                <a href="{{ $routes['contact'] }}" aria-label="Facebook">F</a>
                <a href="{{ $routes['contact'] }}" aria-label="X">X</a>
            </div>
        </div>
        <div class="copyright">
            <p>© {{ date('Y') }} Museum Azman. All rights reserved.</p>
            <p>Privacy Policy &nbsp; Terms of Use</p>
        </div>
    </footer>

    <div class="landing-lightbox" id="landingLightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Image preview">
        <button type="button" data-landing-lightbox-close aria-label="Close image preview">&times;</button>
        <img src="" alt="" data-landing-lightbox-image>
        <p data-landing-lightbox-caption></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const revealItems = document.querySelectorAll(
                '.section-head, .center-copy, .text-panel, .contact-grid, .space-grid, .visit-stats, .program-grid, .value-grid, .grid .card'
            );

            revealItems.forEach((item, index) => {
                item.classList.add('reveal-item');
                if (item.matches('.card')) {
                    item.style.setProperty('--reveal-delay', `${(index % 4) * 90}ms`);
                }
            });

            if (reduceMotion || !('IntersectionObserver' in window)) {
                revealItems.forEach((item) => item.classList.add('is-visible'));
            } else {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -6% 0px',
                });

                revealItems.forEach((item) => revealObserver.observe(item));
            }

            const lightbox = document.getElementById('landingLightbox');
            const image = lightbox?.querySelector('[data-landing-lightbox-image]');
            const caption = lightbox?.querySelector('[data-landing-lightbox-caption]');
            const close = () => {
                if (!lightbox || !image) {
                    return;
                }

                lightbox.classList.remove('is-open');
                lightbox.setAttribute('aria-hidden', 'true');
                image.removeAttribute('src');
                image.alt = '';
                document.body.style.overflow = '';
            };

            document.addEventListener('click', (event) => {
                const targetImage = event.target instanceof Element ? event.target.closest('.card img, .space-grid img, .image-hero img') : null;
                if (!lightbox || !image || !targetImage) {
                    return;
                }

                event.preventDefault();
                const text = targetImage.getAttribute('alt') || targetImage.closest('.card')?.querySelector('h3')?.textContent?.trim() || 'Image preview';
                image.src = targetImage.currentSrc || targetImage.src;
                image.alt = text;
                if (caption) {
                    caption.textContent = text;
                }
                lightbox.classList.add('is-open');
                lightbox.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            });

            lightbox?.addEventListener('click', (event) => {
                if (event.target === lightbox || event.target instanceof Element && event.target.matches('[data-landing-lightbox-close]')) {
                    close();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && lightbox?.classList.contains('is-open')) {
                    close();
                }
            });

            document.querySelectorAll('[data-public-form]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    const response = form.querySelector('[data-form-response]');
                    if (response) {
                        response.textContent = 'Thank you. Your request has been received for review.';
                    }
                    form.reset();
                });
            });
        });
    </script>
</body>
</html>
