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
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
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
            font-family: Georgia, "Times New Roman", serif;
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
            background: rgba(5, 5, 5, 0.78);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(14px);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 190px;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border: 1px solid rgba(255, 255, 255, 0.72);
            color: #fff;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 18px;
            line-height: 1;
        }

        .brand-text {
            display: grid;
            font-size: 11px;
            line-height: 1.02;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: clamp(18px, 2.2vw, 30px);
            color: rgba(255, 255, 255, 0.58);
            font-size: 10px;
            font-weight: 800;
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
            padding: clamp(54px, 7vw, 96px) clamp(28px, 4vw, 76px);
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
            width: min(1160px, 100%);
            margin: 0 auto 42px;
        }

        .section-head.center {
            text-align: center;
        }

        .section-head h2,
        .text-panel h2 {
            font-size: clamp(2rem, 3.2vw, 3.15rem);
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
            width: min(1160px, 100%);
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
        }

        .card img {
            width: 100%;
            aspect-ratio: 4 / 5;
            object-fit: cover;
            border-radius: 2px;
            background: #151515;
            cursor: zoom-in;
        }

        .square-card img,
        .collection-card img {
            aspect-ratio: 1 / 1;
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
            font-family: Georgia, "Times New Roman", serif;
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
            font-size: clamp(2.05rem, 3.6vw, 3.45rem);
            line-height: 1.05;
        }

        .experience p,
        .vision p,
        .center-copy p,
        .text-panel p {
            margin-top: 16px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 15px;
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
            font-family: Georgia, "Times New Roman", serif;
            font-size: 18px;
            font-weight: 400;
        }

        .program-grid p,
        .value-grid p,
        .visit-stats p {
            margin-top: 8px;
            color: var(--muted);
            font-size: 13px;
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
            font-size: 12px;
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
            background: #050505;
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
            <span class="brand-mark">A</span>
            <span class="brand-text"><span>Museum</span><span>Azman</span></span>
        </a>
        <nav class="nav" aria-label="Main navigation">
            @foreach(['about' => 'About', 'events' => 'Events', 'artists' => 'Artists', 'collection' => 'Collection', 'visit' => 'Visit', 'contact' => 'Contact'] as $key => $label)
                <a class="{{ $publicPage === $key ? 'active' : '' }}" href="{{ $routes[$key] }}">{{ $label }}</a>
            @endforeach
        </nav>
    </header>

    <main>
        @if($publicPage === 'home')
            <section class="hero" aria-label="Contemporary art">
                <video autoplay muted loop playsinline poster="{{ $fallbackImage }}">
                    <source src="{{ asset('media/museum-azman-home-video.mp4') }}" type="video/mp4">
                </video>
                <div class="hero-content">
                    <h1>Contemporary art</h1>
                </div>
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
                    <h1 class="page-title">Events</h1>
                    <p class="page-copy">Special exhibitions, artist talks, interviews, and exclusive events exploring contemporary art and cultural dialogue.</p>
                </div>
            </section>

            <section class="section alt">
                <div class="section-head"><h2>Currently Active</h2></div>
                <div class="grid two">
                    @foreach([1, 10] as $loopIndex => $index)
                        <article class="card square-card">
                            <img src="{{ $imageFor($index) }}" alt="{{ ['Silent Forms', 'Curatorial Walkthrough'][$loopIndex] }}" loading="lazy">
                            <h3>{{ ['Silent Forms', 'Curatorial Walkthrough'][$loopIndex] }}</h3>
                            <p>{{ ['Exhibition', 'Guided Tour'][$loopIndex] }}</p>
                            <small>{{ ['On view through June 2026', 'Every Saturday, 2pm'][$loopIndex] }}</small>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="section">
                <div class="section-head"><h2>Upcoming</h2></div>
                <div class="grid three">
                    @foreach([0, 1, 11] as $loopIndex => $index)
                        <article class="card">
                            <img src="{{ $imageFor($index) }}" alt="{{ ['Chromatic Dialogues', 'Artist Talk: Kseniya Lapteva', 'Collectors Evening'][$loopIndex] }}" loading="lazy">
                            <h3>{{ ['Chromatic Dialogues', 'Artist Talk: Kseniya Lapteva', 'Collectors Evening'][$loopIndex] }}</h3>
                            <p>{{ ['Exhibition Opening', 'In Conversation', 'Private Event'][$loopIndex] }}</p>
                            <small>{{ ['March 15, 2026', 'April 8, 2026', 'April 22, 2026'][$loopIndex] }}</small>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="section alt">
                <div class="section-head"><h2>Archive</h2></div>
                <div class="grid three">
                    @foreach([6, 8, 9, 12] as $loopIndex => $index)
                        <article class="card">
                            <img src="{{ $imageFor($index) }}" alt="{{ ['Horizons Exhibition', 'Artist Interview Series', 'Abstract Territories', 'Symposium: Contemporary Art Across Continents'][$loopIndex] }}" loading="lazy">
                            <h3>{{ ['Horizons Exhibition', 'Artist Interview Series', 'Abstract Territories', 'Symposium: Contemporary Art Across Continents'][$loopIndex] }}</h3>
                            <p>{{ ['Margarita Shtyfura', 'Fons Heijnsbroek', 'Group Exhibition', 'Panel Discussion'][$loopIndex] }}</p>
                            <small>{{ ['January - March 2026', 'February 12, 2026', 'October - December 2025', 'September 18, 2025'][$loopIndex] }}</small>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="section">
                <div class="center-copy">
                    <h2>Event Programming</h2>
                    <div class="program-grid">
                        <div><h3>Exhibitions</h3><p>Curated presentations of contemporary art featuring solo and group shows.</p></div>
                        <div><h3>Artist Talks</h3><p>Intimate conversations with artists about their practice and vision.</p></div>
                        <div><h3>Interviews</h3><p>In-depth dialogues exploring artistic processes and cultural contexts.</p></div>
                        <div><h3>Special Events</h3><p>Exclusive gatherings, collector evenings, and symposiums.</p></div>
                    </div>
                </div>
            </section>
        @elseif($publicPage === 'artists')
            <section class="page-intro">
                <div class="page-intro-inner">
                    <h1 class="page-title">Artists</h1>
                    <p class="page-copy">Representing voices from the Americas to Southeast Asia, our artists explore contemporary themes through diverse mediums and perspectives.</p>
                </div>
            </section>

            <section class="section">
                <div class="grid four">
                    @foreach([3, 4, 5, 6, 7, 8, 9, 10] as $loopIndex => $index)
                        <article class="card artist-card">
                            <img src="{{ $imageFor($index) }}" alt="{{ $artistFor($index, 'Artist') }}" loading="lazy">
                            <h3>{{ $artistFor($index, ['Aurelius Wendleken', 'Fons Heijnsbroek', 'Kseniya Lapteva', 'Margarita Shtyfura', 'Chen Wei', 'Anh Vy', 'Sofia Ramirez', 'Elena Torres'][$loopIndex]) }}</h3>
                            <small>{{ $countryFor($index, ['Germany', 'Netherlands', 'Russia', 'Ukraine', 'China', 'Vietnam', 'Mexico', 'Brazil'][$loopIndex]) }}</small>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="section alt">
                <div class="center-copy">
                    <h2>Artist Collaborations</h2>
                    <p>Museum Azman works directly with artists to create meaningful exhibitions that honor their vision while facilitating dialogue with audiences. We are committed to supporting artistic practice through acquisitions, commissions, and cultural exchange.</p>
                </div>
            </section>
        @elseif($publicPage === 'collection')
            <section class="page-intro">
                <div class="page-intro-inner">
                    <h1 class="page-title">Collection</h1>
                    <p class="page-copy">Our permanent collection represents significant voices in contemporary art, spanning diverse mediums, geographies, and artistic approaches.</p>
                </div>
            </section>

            <section class="section">
                <div class="grid three">
                    @foreach(range(0, 8) as $index)
                        <article class="card collection-card">
                            <img src="{{ $imageFor($index) }}" alt="{{ $titleFor($index, 'Collection artwork') }}" loading="lazy">
                            <h3>{{ $titleFor($index, ['Chromatic Resonance', 'Abstract Composition III', 'Geometric Harmony', 'Pink Dreams', 'Expressive Forms', 'Distant Shores', 'Vivid Emotions', 'Fluid Transitions', 'Color Study'][$index]) }}</h3>
                            <p>{{ $artistFor($index) }}, {{ $yearFor($index, '2024') }}</p>
                            <small>{{ $mediumFor($index, 'Oil on canvas') }}</small>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="section alt">
                <div class="text-panel">
                    <h2>Collecting Philosophy</h2>
                    <p>Museum Azman's collection is built on a commitment to artistic excellence and cultural dialogue. We acquire works that challenge conventions, expand perspectives, and demonstrate enduring relevance.</p>
                    <p>Our focus on artists from the Americas to Southeast Asia reflects our belief that these regions offer vital perspectives often underrepresented in global art discourse. The collection grows through careful consideration, prioritizing depth over breadth.</p>
                    <p>Each work is selected not only for its individual merit but for its contribution to the larger narrative we are building, one that honors diverse artistic traditions while embracing contemporary innovation.</p>
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
                    <form class="public-form" data-public-form>
                        <p class="form-section-title">Personal Information</p>
                        <div class="form-grid">
                            <div class="field"><label for="visit-name">Full Name *</label><input id="visit-name" name="name" required></div>
                            <div class="field"><label for="visit-phone">Phone Number *</label><input id="visit-phone" name="phone" required></div>
                            <div class="field full"><label for="visit-email">Email Address *</label><input id="visit-email" name="email" type="email" required></div>
                            <div class="field"><label for="visit-occupation">Occupation *</label><input id="visit-occupation" name="occupation" required></div>
                            <div class="field"><label for="visit-company">Company / Organisation *</label><input id="visit-company" name="company" required></div>
                            <div class="field full"><label for="visit-city">City / Country *</label><input id="visit-city" name="city" required></div>
                            <div class="field full"><label for="visit-social">Social Media Profile (Optional)</label><input id="visit-social" name="social" placeholder="Instagram, LinkedIn, etc."></div>
                        </div>
                        <p class="form-section-title">Visit Details</p>
                        <div class="form-grid">
                            <div class="field full"><label for="visit-purpose">Purpose of Visit *</label><select id="visit-purpose" name="purpose" required><option value="">Select purpose</option><option>Collector viewing</option><option>Curatorial research</option><option>Artist visit</option><option>Private tour</option></select></div>
                            <div class="field full"><label for="visit-category">Interest Category *</label><select id="visit-category" name="category" required><option value="">Select category</option><option>Contemporary painting</option><option>Southeast Asian art</option><option>Acquisition inquiry</option><option>General viewing</option></select></div>
                            <div class="field"><label for="visit-date">Preferred Visit Date *</label><input id="visit-date" name="date" type="date" required></div>
                            <div class="field"><label for="visit-guests">Number of Guests *</label><input id="visit-guests" name="guests" type="number" min="1" max="6" value="1" required></div>
                            <div class="field full"><label for="visit-source">How Did You Hear About Us? *</label><select id="visit-source" name="source" required><option value="">Select source</option><option>Collector referral</option><option>Artist referral</option><option>Social media</option><option>Press or publication</option></select></div>
                            <div class="field full"><label for="visit-message">Message / Special Requests</label><textarea id="visit-message" name="message" placeholder="Share any specific interests, questions, or accessibility requirements"></textarea></div>
                        </div>
                        <p class="form-section-title">Preferences</p>
                        <div class="checkbox-list">
                            <label><input type="checkbox" name="preference[]" value="outside-hours"> Request exclusive private viewing outside regular hours</label>
                            <label><input type="checkbox" name="preference[]" value="curator"> Request guided tour with curator</label>
                            <label><input type="checkbox" name="preference[]" value="events"> Receive invitations to exclusive events, talks, and special programming</label>
                            <label><input type="checkbox" name="preference[]" value="updates"> Receive updates about future public opening plans</label>
                            <label><input type="checkbox" required> I agree to the privacy policy and consent to Museum Azman storing and processing my information for coordinating my visit. *</label>
                        </div>
                        <button type="submit">Submit Request</button>
                        <p class="form-response" data-form-response></p>
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
                            <div class="contact-item"><span class="contact-icon">T</span><div><h3>Phone</h3><p>+1 (234) 567-8900</p></div></div>
                            <div class="contact-item"><span class="contact-icon">L</span><div><h3>Location</h3><p>Museum Azman<br>By Invitation Only<br>Location disclosed upon registration</p></div></div>
                        </div>
                        <div class="text-panel" style="margin: 48px 0 0; border-top: 1px solid var(--line); padding-top: 34px;">
                            <h3>Visiting Hours</h3>
                            <p>By appointment only<br>Tuesday - Saturday<br>10:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                    <form class="public-form" data-public-form>
                        <div class="field"><label for="contact-name">Name *</label><input id="contact-name" name="name" required></div>
                        <div class="field"><label for="contact-email">Email *</label><input id="contact-email" name="email" type="email" required></div>
                        <div class="field"><label for="contact-subject">Subject</label><input id="contact-subject" name="subject"></div>
                        <div class="field"><label for="contact-message">Message *</label><textarea id="contact-message" name="message" required></textarea></div>
                        <button type="submit">Send Message</button>
                        <p class="form-response" data-form-response></p>
                    </form>
                </div>
            </section>
        @elseif($publicPage === 'about')
            <section class="image-hero">
                <img src="{{ $imageFor(11) }}" alt="About Museum Azman" loading="eager">
                <div class="image-hero-content">
                    <h1>About Museum Azman</h1>
                    <p>A sanctuary for contemporary art across continents</p>
                </div>
            </section>

            <section class="section">
                <div class="text-panel">
                    <h2>Our Mission</h2>
                    <p>Museum Azman was founded to create a dialogue between contemporary artists across the Americas and Southeast Asia, regions rich with cultural heritage and innovative artistic practices.</p>
                    <p>We believe art transcends borders and speaks to universal human experiences while honoring distinct cultural perspectives. Our collection represents diverse voices exploring themes of identity, place, memory, and transformation.</p>
                    <p>Operating as a private museum allows us to maintain an intimate, contemplative environment where visitors can engage deeply with artworks without distraction. Each viewing is curated to facilitate meaningful encounters between art and observer.</p>
                </div>
            </section>

            <section class="section alt">
                <div class="text-panel">
                    <h2>Looking Forward</h2>
                    <p>While currently invitation-only, we are planning to open Museum Azman to the public in the coming years. This transition will expand access while preserving the qualities that make our space unique: thoughtful curation, intimate scale, and commitment to deep engagement.</p>
                    <p>Our future plans include educational programming, artist residencies, and cultural exchange initiatives that further our mission of connecting artistic communities across continents.</p>
                    <p style="color: var(--gold-soft); font-weight: 700;">We invite collectors, curators, artists, and serious art enthusiasts to visit and join us on this journey.</p>
                </div>
            </section>

            <section class="section">
                <div class="center-copy">
                    <h2>Our Values</h2>
                    <div class="value-grid">
                        <div><h3>Cultural Exchange</h3><p>Fostering dialogue between artistic traditions and contemporary practices across continents.</p></div>
                        <div><h3>Intimate Experience</h3><p>Creating space for contemplation and deep engagement with art in a serene environment.</p></div>
                        <div><h3>Artistic Excellence</h3><p>Presenting works of exceptional quality that challenge, inspire, and endure.</p></div>
                    </div>
                </div>
            </section>

            <section class="section alt">
                <div class="space-grid">
                    <div>
                        <h2>The Space</h2>
                        <p class="page-copy">Museum Azman occupies a minimalist structure designed to let art breathe. Natural light, clean lines, and deliberate proportions create galleries that enhance rather than compete with the works on display.</p>
                        <p class="page-copy">Our architecture reflects the museum's philosophy: restraint, clarity, and respect for the power of art to speak for itself.</p>
                    </div>
                    <img src="{{ $imageFor(12) }}" alt="Museum Azman space" loading="lazy">
                </div>
            </section>
        @endif
    </main>

    <footer class="footer">
        <div>
            <a class="brand" href="{{ $routes['home'] }}" aria-label="Museum Azman home">
                <span class="brand-mark">A</span>
                <span class="brand-text"><span>Museum</span><span>Azman</span></span>
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
