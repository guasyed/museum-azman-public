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
        @font-face {
            font-family: "Cormorant Garamond";
            src: url("{{ asset('fonts/cormorant-garamond-regular.ttf') }}") format("truetype");
            font-style: normal;
            font-weight: 400;
            font-display: swap;
        }

        @font-face {
            font-family: "Cormorant Garamond";
            src: url("{{ asset('fonts/cormorant-garamond-italic.ttf') }}") format("truetype");
            font-style: italic;
            font-weight: 400;
            font-display: swap;
        }

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
            --public-font-serif: "Cormorant Garamond", Georgia, "Times New Roman", serif;
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
            /*font-family: var(--public-font-sans);*/
            font-family: "Cormorant Garamond", Georgia, serif !important;
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
            font-family: "Inter", Arial, sans-serif;
        }

        .nav a {
            transition: color 160ms ease;
        }

        .nav a:hover,
        .nav a.active {
            color: var(--gold-soft);
        }

        .nav-toggle {
            display: none;
            width: 44px;
            height: 44px;
            padding: 11px 9px;
            border: 0;
            background: transparent;
            color: #fff;
            cursor: pointer;
        }

        .nav-toggle span {
            display: block;
            width: 100%;
            height: 1px;
            margin: 6px 0;
            background: currentColor;
            transition: transform 220ms ease, opacity 180ms ease;
        }

        .hero {
            position: relative;
            min-height: 620px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-bottom: 1px solid var(--line);
        }

        .hero .hero-media,
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
            font-size: 11px;
        }

        .public-page-home {
            --home-ivory: #e8e1d4;
            --home-gold: #b79a55;
            --home-artwork-mat: #d9d6cd;
            background: #070806;
        }

        .public-page-home .site-header {
            min-height: 98px;
            padding: 14px clamp(34px, 6.2vw, 106px);
            background: linear-gradient(90deg, #17140f 0%, #202120 46%, #201f1d 100%);
        }

        .public-page-home .brand-logo,
        .public-page-about .brand-logo,
        .public-page-events .brand-logo,
        .public-page-collection .brand-logo {
            width: 132px;
        }

        .public-page-home .nav,
        .public-page-about .nav,
        .public-page-events .nav,
        .public-page-collection .nav {
            margin-left: auto;
            margin-right: auto;
            gap: 28px;
            font-size: 10px;
            letter-spacing: .18em;
        }

        .home-access-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 33px;
            min-width: 140px;
            margin-left: 0;
            padding: 0 17px;
            border: 1px solid rgba(255,255,255,.25);
            color: rgba(255,255,255,.82);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
            transition: border-color 250ms ease, background 250ms ease;
            font-family: "Inter", Arial, sans-serif;
        }

        .home-access-link:hover {
            border-color: var(--home-gold);
            background: rgba(183,154,85,.12);
        }

        .public-page-home .hero {
            min-height: clamp(620px, 76vh, 681px);
            margin-top: 98px;
        }

        .public-page-home .hero .hero-media {
            opacity: .74;
        }

        .public-page-home .hero::after {
            background: linear-gradient(180deg, rgba(0,0,0,.32), rgba(0,0,0,.54));
        }

        .public-page-home .hero-content {
            width: min(900px, calc(100% - 40px));
            padding-top: 0;
            top: -42px;
        }

        .public-page-home .hero h1 {
            font-family: var(--public-font-sans);
            font-size: clamp(3rem, 5vw, 5rem);
            font-weight: 300;
            line-height: 1;
            letter-spacing: .17em;
            color: #fff;
            text-transform: uppercase;
        }

        .home-hero-copy {
            max-width: 480px;
            margin: 36px auto 0;
            color: rgba(255,255,255,.9);
            font-size: 17px;
            line-height: 1.55;
        }

        .home-eyebrow {
            display: block;
            margin-top: 28px;
            color: var(--home-gold);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .home-primary-button,
        .home-outline-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            min-height: 54px;
            margin-top: 38px;
            padding: 0 28px;
            border: 1px solid rgba(255,255,255,.3);
            background: rgba(255,255,255,.9);
            color: #161713;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
            transition: transform 250ms ease, background 250ms ease;
        }

        .home-primary-button:hover,
        .home-outline-button:hover {
            transform: translateY(-2px);
            background: #fff;
        }

        .public-page-home .home-section {
            padding: clamp(68px, 7vw, 68px) clamp(28px, 3.3vw, 58px);
            background: #070806;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .home-section-inner {
            width: 100%;
            margin: 0 auto;
        }

        .public-page-home .home-section .grid {
            gap: clamp(24px, 2vw, 32px);
        }

        .public-page-home .home-section .section-head {
            margin-bottom: 32px;
        }

        .public-page-home .home-section .section-head h2 {
            color: var(--home-ivory);
            font-size: clamp(2rem, 2.8vw, 44px);
            line-height: 1.15;
        }

        .public-page-home .home-section .section-head p {
            margin-top: 10px;
            color: rgba(255,255,255,.55);
            font-size: 18px;
            line-height: 1.45;
        }

        .public-page-home .home-programmes .card img,
        .public-page-home .home-programmes .event-placeholder {
            aspect-ratio: 5 / 6;
            object-fit: cover;
            box-shadow: 0 17px 0 -16px rgba(255,255,255,.11);
        }

        .public-page-home .home-card-overline {
            display: block;
            margin-top: 28px;
            color: var(--home-gold);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .public-page-home .home-card h3 {
            margin-top: 10px;
            color: var(--home-ivory);
            font-family: var(--public-font-serif);
            font-size: clamp(22px, 1.65vw, 29px);
            font-weight: 400;
            line-height: 1.15;
        }

        .public-page-home .home-card p {
            margin-top: 12px;
            color: rgba(255,255,255,.54);
            font-size: 16px;
            line-height: 1.5;
        }

        .public-page-home .collection-frame {
            display: grid;
            width: 100%;
            aspect-ratio: 5 / 6;
            place-items: center;
            padding: 14px;
            background: var(--home-artwork-mat);
            overflow: hidden;
            box-shadow: 0 17px 0 -16px rgba(255,255,255,.11);
        }

        .public-page-home .collection-frame img {
            width: 100%;
            height: 100%;
            max-height: none;
            object-fit: contain;
            border-radius: 0;
            background: var(--home-artwork-mat);
        }

        .public-page-home .collection-meta {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 18px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, .12);
        }

        .public-page-home .collection-card .collection-meta h3 {
            margin-top: 0;
            color: rgba(239, 234, 224, .94);
            font-size: 22px;
            line-height: 1.12;
            transition: color 220ms ease;
        }

        .public-page-home .collection-card .collection-meta p {
            margin-top: 10px;
            color: rgba(255, 255, 255, .6);
            font-size: 14px;
            font-weight: 400;
        }

        .public-page-home .collection-card .collection-meta .home-card-overline {
            flex: 0 0 auto;
            margin-top: 0;
            color: rgba(255, 255, 255, .52);
            font-size: 10px;
            font-weight: 500;
            letter-spacing: .18em;
        }

        .public-page-home .collection-card:hover .collection-meta h3,
        .public-page-home .collection-card:focus-visible .collection-meta h3 {
            color: var(--home-gold);
        }

        .public-page-home .home-collection-focus {
            padding-bottom: 70px;
        }

        .public-page-home .home-collection-focus .section-link {
            margin-top: 42px;
            font-size: 10px;
            letter-spacing: .18em;
        }

        .public-page-home .home-collection-focus .collection-card > small {
            margin-top: 8px;
            color: rgba(255, 255, 255, .4);
            font-size: 10px;
            font-weight: 500;
            letter-spacing: .18em;
        }

        .public-page-home .experience {
            min-height: 365px;
            padding: 44px 22px;
        }

        .public-page-home .experience h2 {
            color: var(--home-ivory);
            font-size: clamp(2.25rem, 3vw, 46px);
            line-height: 1.08;
        }

        .public-page-home .experience p {
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 24px;
            font-size: 18px;
            line-height: 1.65;
        }

        .public-page-home .experience .home-outline-button {
            min-width: 250px;
            min-height: 62px;
            margin-top: 30px;
            padding: 0 28px;
            border-color: rgba(255,255,255,.38);
            background: rgba(0,0,0,.14);
            color: rgba(255,255,255,.9);
            font-size: 10px;
            letter-spacing: .18em;
        }

        .public-page-home .hero .home-primary-button {
            min-width: 262px;
            min-height: 54px;
            background: rgba(238,235,229,.94);
        }

        .public-page-home .experience .home-outline-button:hover {
            border-color: rgba(255,255,255,.68);
            background: rgba(255,255,255,.1);
        }

        .home-story {
            padding: 63px clamp(28px, 3.3vw, 56px) 64px;
            background: #171410;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .home-story-inner {
            width: 100%;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(300px, .95fr);
            gap: clamp(52px, 5.6vw, 96px);
            align-items: center;
        }

        .home-story-image {
            position: relative;
            overflow: hidden;
            background: #0c0c0b;
        }

        .home-story-image::after {
            content: "";
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            height: 34%;
            background: linear-gradient(180deg, transparent, rgba(0, 0, 0, .56));
            pointer-events: none;
        }

        .home-story-image img {
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            filter: brightness(.68) saturate(.84) contrast(1.03) sepia(.02);
            transition: transform 800ms cubic-bezier(.22,1,.36,1), filter 350ms ease;
        }

        .home-story-image:hover img {
            transform: scale(1.035);
            filter: brightness(.72) saturate(.87) contrast(1.04) sepia(.02);
        }

        .home-story-label {
            position: absolute;
            z-index: 1;
            left: 24px;
            bottom: 20px;
            color: rgba(255,255,255,.96);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .18em;
            line-height: 1.3;
            text-shadow: 0 1px 5px rgba(0,0,0,.9);
            text-transform: uppercase;
        }

        .home-story-copy small {
            color: var(--home-gold);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .home-story-copy h2 {
            max-width: 430px;
            margin-top: 22px;
            color: var(--home-ivory);
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: clamp(3.25rem, 4.2vw, 68px);
            font-weight: 500;
            letter-spacing: -.021em;
            line-height: .93;
            text-wrap: balance;
        }

        .home-story-copy p {
            max-width: 620px;
            margin-top: 105px;
            color: rgba(255,255,255,.62);
            font-size: 15px;
            line-height: 1.85;
        }

        .home-text-link {
            display: inline-flex;
            align-items: center;
            gap: 16px;
            margin-top: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--home-gold);
            color: rgba(244, 240, 232, .9);
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .2em;
            text-transform: uppercase;
            transition: color 180ms ease, border-color 180ms ease;
        }

        .home-text-link:hover {
            border-color: var(--gold-soft);
            color: #fff;
        }

        .public-page-home .vision {
            padding: 64px 22px 66px;
        }

        .public-page-home .vision h2 {
            color: var(--home-ivory);
            font-size: clamp(2.15rem, 2.7vw, 44px);
            line-height: 1.1;
        }

        .public-page-home .vision p {
            margin-top: 24px;
            font-size: 18px;
            line-height: 1.6;
        }

        .home-connect {
            padding: 65px 22px 64px;
            text-align: center;
            background: #0d0e0c;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .home-connect h2 {
            color: var(--home-ivory);
            font-size: clamp(2rem, 2.3vw, 36px);
            line-height: 1.12;
        }

        .home-connect p {
            max-width: 720px;
            margin: 26px auto 0;
            color: rgba(255,255,255,.57);
            font-size: 18px;
            line-height: 1.55;
        }

        .home-connect .home-primary-button {
            min-width: 208px;
            min-height: 59px;
            margin-top: 30px;
            background: #c7aa66;
            border-color: #c7aa66;
            font-size: 10px;
            letter-spacing: .18em;
        }

        .experience {
            position: relative;
            min-height: 360px;
            display: grid;
            place-items: center;
            overflow: hidden;
            background-image: linear-gradient(rgba(0, 0, 0, 0.54), rgba(0, 0, 0, 0.58)), url("{{ $homeExperienceBackgroundUrl ?: $imageFor(10) }}");
            background-size: cover;
            background-position: center;
            text-align: center;
        }

        .public-page-about {
            background: #070806;
        }

        .public-page-about .site-header {
            min-height: 98px;
            padding: 14px clamp(34px, 6.2vw, 106px);
            background: linear-gradient(90deg, #17140f 0%, #202120 46%, #201f1d 100%);
            font-family: "Cormorant Garamond", Georgia, serif !important;
        }

        .public-page-about .image-hero {
            min-height: clamp(420px, 26vw, 470px);
            margin-top: 98px;
            place-items: end start;
        }

        .public-page-about .image-hero img {
            opacity: .78;
        }

        .public-page-about .image-hero::after {
            background: linear-gradient(90deg, rgba(0, 0, 0, .52), rgba(0, 0, 0, .2));
        }

        .public-page-about .image-hero-content {
            width: 100%;
            max-width: none;
            padding: 0 clamp(28px, 3.3vw, 56px) 52px;
            text-align: left;
        }

        .public-page-about .image-hero-content h1 {
            max-width: 590px;
            color: #eee9df;
            font-size: clamp(3.6rem, 5.2vw, 6rem);
            line-height: .96;
            font-family: "Cormorant Garamond", Georgia, serif !important;
        }

        .public-page-about .image-hero-content p {
            margin-top: 18px;
            color: rgba(255, 255, 255, .68);
            font-size: 20px;
        }

        .public-page-about .section {
            padding: 62px 22px 70px;
        }

        .public-page-about .section.alt {
            background: #0d0d0c;
        }

        .public-page-about .text-panel {
            width: min(900px, calc(100% - 44px));
        }

        .public-page-about .text-panel h2,
        .public-page-about .center-copy h2,
        .public-page-about .space-grid h2 {
            color: #eee9df;
            font-size: 44px;
            line-height: 1.08;
        }

        .public-page-about .text-panel p,
        .public-page-about .space-grid .page-copy {
            margin-top: 16px;
            color: rgba(255, 255, 255, .67);
            font-size: 20px;
            line-height: 1.48;
        }

        .public-page-about .center-copy {
            width: min(1120px, 100%);
        }

        .public-page-about .value-grid {
            width: min(1120px, 100%);
            gap: 44px;
            margin-top: 42px;
        }

        .public-page-about .value-grid h3 {
            color: #eee9df;
            font-size: 23px;
        }

        .public-page-about .value-grid p {
            color: rgba(255, 255, 255, .64);
            font-size: 18px;
            line-height: 1.5;
        }

        .public-page-about .about-values {
            padding: 18px 22px 76px;
        }

        .public-page-about .about-values .value-grid {
            width: min(1160px, 100%);
            gap: 64px;
            margin-top: 38px;
        }

        .public-page-about .about-space {
            padding: 53px 22px 58px;
        }

        .public-page-about .space-grid {
            width: min(1152px, calc(100% - 44px));
            gap: 32px;
            align-items: center;
        }

        .public-page-about .space-grid img {
            aspect-ratio: 4 / 3;
        }

        .public-page-events {
            --programme-gold: #b79a55;
            --programme-ivory: #e9e3d7;
            --home-gold: #b79a55;
            background: #070806;
        }

        .public-page-events .site-header {
            min-height: 98px;
            padding: 14px clamp(34px, 6.2vw, 106px);
            background: linear-gradient(90deg, #17140f 0%, #202120 46%, #201f1d 100%);
        }

        .programme-hero {
            display: grid;
            grid-template-columns: 1.08fr .92fr;
            height: 680px;
            min-height: 0;
            margin-top: 98px;
            background: #11110f;
            border-bottom: 1px solid var(--line);
            overflow: hidden;
        }

        .programme-hero-copy {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
            min-height: 0;
            padding: 64px clamp(34px, 3.3vw, 56px);
            overflow: hidden;
        }

        .programme-kicker,
        .programme-overline {
            color: var(--programme-gold);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .22em;
            text-transform: uppercase;
            font-family: "Inter", Arial, sans-serif;
        }

        .programme-hero h1 {
            margin-top: 34px;
            color: var(--programme-ivory);
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: clamp(4.5rem, 8.8vw, 9.4rem);
            font-weight: 400;
            letter-spacing: -.035em;
            line-height: .86;
        }

        .programme-hero h1 em {
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-style: italic;
            font-weight: 400;
            letter-spacing: -.045em;
        }

        .programme-hero-summary {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 28px;
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .programme-hero-summary p {
            color: rgba(255,255,255,.64);
            font-size: 15px;
            line-height: 1.6;
            font-family: "Inter", Arial, sans-serif;
        }

        .programme-hero-image {
            width: calc(100% + 100px);
            height: 680px;
            margin-left: -100px;
            min-width: 0;
            min-height: 0;
            overflow: hidden;
            -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0, 0, 0, .35) 7%, #000 18%, #000 100%);
            mask-image: linear-gradient(to right, transparent 0%, rgba(0, 0, 0, .35) 7%, #000 18%, #000 100%);
        }

        .programme-hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(1);
        }

        .programme-list {
            padding: 26px clamp(34px, 6.1vw, 105px) 18px;
            background: #070806;
        }

        .programme-list-inner,
        .programme-editorial-inner,
        .programme-preparation-inner,
        .programme-research-inner {
            width: min(1645px, 100%);
            margin: 0 auto;
        }

        .programme-list-heading {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 50px;
            align-items: end;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--line);
        }

        .programme-list-heading h2,
        .programme-preparation h2 {
            margin-top: 15px;
            color: var(--programme-ivory);
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: clamp(2.8rem, 3.5vw, 3.75rem);
            font-weight: 400;
            letter-spacing: -.02em;
            line-height: 1;
        }

        .programme-list-heading p {
            color: rgba(255,255,255,.62);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            line-height: 1.6;
        }

        .programme-row {
            display: grid;
            grid-template-columns: 64px minmax(460px, 1.15fr) minmax(350px, .8fr) 190px;
            gap: 48px;
            align-items: center;
            min-height: 200px;
            border-bottom: 1px solid var(--line);
        }

        .programme-row:first-of-type {
            min-height: 230px;
        }

        .programme-number {
            color: rgba(255,255,255,.32);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 31px;
            font-weight: 300;
        }

        .programme-row h3 {
            margin-top: 10px;
            color: #fff;
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: clamp(2.35rem, 2.7vw, 2.9rem);
            font-weight: 400;
            letter-spacing: -.02em;
            line-height: 1.05;
            transition: color .2s ease;
        }

        .programme-row:hover h3 {
            color: var(--programme-gold);
        }

        .programme-row-description {
            color: rgba(255,255,255,.64);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            line-height: 1.6;
        }

        .programme-row img {
            width: 190px;
            height: 144px;
            object-fit: cover;
        }

        .programme-action {
            display: inline-block;
            margin-top: 28px;
            padding-bottom: 8px;
            /* border-bottom: 1px solid var(--programme-gold); */
            color: var(--programme-ivory);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .programme-action:hover {
            color: var(--programme-gold);
        }

        .programme-list .programme-action {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            max-width: 100%;
            white-space: nowrap;
        }

        .programme-editorial {
            padding: 0;
            overflow: hidden;
            background: #0c0c0b;
            border-block: 1px solid var(--line);
        }

        .programme-editorial-inner {
            display: grid;
            grid-template-columns: .45fr .55fr;
            width: 100%;
            max-width: none;
            height: clamp(430px, 25.5vw, 445px);
            min-height: 0;
        }

        .programme-editorial-copy {
            display: flex;
            align-items: flex-start;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            padding: 48px clamp(42px, 5.6vw, 96px);
        }

        .programme-editorial h2 {
            max-width: 560px;
            margin-top: 18px;
            color: var(--programme-ivory);
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: clamp(3.2rem, 3.7vw, 4rem);
            font-weight: 400;
            letter-spacing: -.025em;
            line-height: .9;
            text-wrap: balance;
        }

        .programme-editorial p {
            max-width: 480px;
            margin-top: 16px;
            color: rgba(255,255,255,.66);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            line-height: 1.85;
        }

        .programme-editorial-copy .programme-action {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            width: 220px;
            max-width: 100%;
            margin-top: 28px;
            padding-bottom: 11px;
            border-color: var(--programme-gold);
            color: rgba(239, 234, 224, .92);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .2em;
            line-height: 1;
            white-space: nowrap;
            transition: color 180ms ease, border-color 180ms ease;
        }

        .programme-editorial-copy .programme-action span:last-child {
            font-size: 15px;
            letter-spacing: 0;
            transition: transform 180ms ease;
        }

        .programme-editorial-copy .programme-action:hover,
        .programme-editorial-copy .programme-action:focus-visible {
            border-color: var(--gold-soft);
            color: var(--gold-soft);
        }

        .programme-editorial-copy .programme-action:hover span:last-child,
        .programme-editorial-copy .programme-action:focus-visible span:last-child {
            transform: translate(3px, -3px);
        }

        .programme-editorial-image {
            position: relative;
            width: 90%;
            height: 100%;
            min-height: 0;
            overflow: hidden;
            padding-inline: clamp(40px, 3.5vw, 60px);
            background: #262116;
        }

        .programme-editorial-image::after {
            content: "";
            position: absolute;
            inset: 45% 0 0;
            background: linear-gradient(180deg, transparent, rgba(0,0,0,.62));
            pointer-events: none;
        }

        .programme-editorial-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            filter: brightness(.7) saturate(.86) contrast(1.02);
        }

        .programme-editorial-image h3 {
            position: absolute;
            z-index: 1;
            left: 48px;
            bottom: 34px;
            color: #eee9df;
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: clamp(2.2rem, 3vw, 3.25rem);
            font-weight: 400;
            letter-spacing: -.025em;
        }

        .programme-editorial-label {
            position: absolute;
            z-index: 1;
            left: 48px;
            bottom: 98px;
            color: rgba(255,255,255,.72);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .programme-preparation {
            min-height: 535px;
            padding: 64px clamp(34px, 6.1vw, 105px);
            background: #070806;
        }

        .programme-preparation-intro {
            max-width: 760px;
        }

        .programme-preparation-intro > p {
            margin-top: 20px;
            color: rgba(255,255,255,.64);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            line-height: 1.55;
        }

        .programme-coming-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            margin-top: 36px;
        }

        .programme-coming-card {
            position: relative;
            min-height: 240px;
            padding: 28px;
            border: 0;
        }

        .programme-coming-card + .programme-coming-card {
            border-left: 1px solid rgba(255,255,255,.13);
        }

        .programme-coming-card h3 {
            margin-top: 12px;
            color: var(--programme-ivory);
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: clamp(2rem, 2.3vw, 2.35rem);
            font-weight: 400;
            letter-spacing: -.02em;
            line-height: 1.05;
        }

        .programme-coming-card p {
            max-width: 500px;
            margin-top: 15px;
            color: rgba(255,255,255,.64);
            font-size: 15px;
            line-height: 1.55;
            font-family: Arial, Helvetica, sans-serif;
        }

        .programme-coming-badge {
            position: absolute;
            top: 28px;
            right: 24px;
            padding: 6px 10px;
            border: 1px solid rgba(255,255,255,.16);
            color: rgba(255,255,255,.6);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .programme-coming-icon {
            display: block;
            width: 22px;
            height: 22px;
            margin-bottom: 28px;
            color: var(--programme-gold);
        }

        .programme-research {
            min-height: 290px;
            padding: 54px clamp(34px, 3.3vw, 58px);
            background: #15130f;
            border-block: 1px solid var(--line);
        }

        .programme-research-inner {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: clamp(120px, 18vw, 300px);
            align-items: center;
            width: 100%;
            max-width: none;
        }

        .programme-research h2 {
            max-width: 650px;
            margin-top: 18px;
            color: var(--programme-ivory);
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: clamp(3.2rem, 4vw, 4.2rem);
            font-weight: 400;
            letter-spacing: -.025em;
            line-height: .92;
        }

        .programme-research h2 em {
            font-style: italic;
            font-weight: 400;
        }

        .programme-research p {
            max-width: 610px;
            margin-top: 22px;
            color: rgba(255,255,255,.68);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
            line-height: 1.55;
        }

        .programme-research-icon {
            width: 20px;
            height: 20px;
            color: var(--programme-gold);
        }

        .programme-research-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 13px;
            min-width: 225px;
            min-height: 40px;
            margin-top: 28px;
            padding: 0 22px;
            border: 1px solid rgba(255,255,255,.28);
            background: #eeeae3;
            color: #1a1916;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            font-weight: 500;
            letter-spacing: .19em;
            text-transform: uppercase;
            transition: background 180ms ease, transform 180ms ease;
        }

        .programme-research-button:hover,
        .programme-research-button:focus-visible {
            background: #fff;
            transform: translateY(-2px);
        }

        .programme-research-button svg {
            width: 14px;
            height: 14px;
            flex: 0 0 auto;
        }

        .public-page-collection {
            --collection-gold: #b79a55;
            --collection-ivory: #e9e3d7;
            --home-gold: #b79a55;
            background: #070806;
        }

        .public-page-collection .site-header {
            min-height: 98px;
            padding: 14px clamp(34px, 6.2vw, 106px);
            background: linear-gradient(90deg, #17140f 0%, #202120 46%, #201f1d 100%);
        }

        .collection-index-intro {
            margin-top: 98px;
            padding: 52px clamp(34px, 3.8vw, 58px) 48px;
            border-bottom: 1px solid var(--line);
            background: #070806;
        }

        .collection-index-intro-inner {
            width: min(1500px, 100%);
            margin: 0 auto;
        }

        .collection-index-intro h1 {
            color: var(--collection-ivory);
            font-size: clamp(3.2rem, 4.8vw, 5rem);
            line-height: 1;
        }

        .collection-index-intro p {
            max-width: 720px;
            margin-top: 26px;
            color: rgba(255,255,255,.64);
            font-size: 18px;
            line-height: 1.55;
        }

        .collection-artist-index {
            padding: 0 clamp(34px, 6.5vw, 105px) 48px;
            background: #070806;
        }

        .collection-artist-index-inner,
        .collection-reading-inner,
        .collection-philosophy-inner {
            width: min(1500px, 100%);
            margin: 0 auto;
        }

        .collection-index-heading {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 60px;
            align-items: end;
            padding: 20px 0 28px;
            border-bottom: 1px solid var(--line);
        }

        .collection-overline {
            color: var(--collection-gold);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .collection-index-heading h2 {
            margin-top: 12px;
            color: var(--collection-ivory);
            font-size: clamp(2.5rem, 3.6vw, 3.8rem);
            line-height: 1;
        }

        .collection-index-heading p {
            color: rgba(255,255,255,.58);
            font-size: 14px;
            line-height: 1.55;
        }

        .collection-artist-row {
            display: grid;
            grid-template-columns: 54px 1fr auto;
            gap: 28px;
            align-items: center;
            min-height: 86px;
            border-bottom: 1px solid var(--line);
        }

        .collection-artist-number,
        .collection-artist-country {
            color: rgba(255,255,255,.46);
            font-size: 9px;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .collection-artist-row h3 {
            color: #fff;
            font-size: clamp(2rem, 2.7vw, 3rem);
            line-height: 1;
            transition: color .2s ease;
        }

        .collection-artist-row:hover h3 {
            color: var(--collection-gold);
        }

        .collection-reading {
            padding: 50px clamp(34px, 6.5vw, 105px);
            background: #10100f;
            border-block: 1px solid var(--line);
        }

        .collection-reading-inner {
            display: grid;
            grid-template-columns: 420px 1px 1fr;
            gap: 48px;
            align-items: center;
        }

        .collection-reading-divider {
            width: 1px;
            height: 105px;
            background: var(--line);
        }

        .collection-reading h2 {
            margin-top: 14px;
            color: var(--collection-ivory);
            font-size: clamp(2.8rem, 4.2vw, 4.5rem);
            line-height: .92;
        }

        .collection-reading p {
            max-width: 680px;
            color: rgba(255,255,255,.64);
            font-size: 16px;
            line-height: 1.55;
        }

        .collection-reading a {
            display: inline-block;
            margin-top: 22px;
            padding-bottom: 7px;
            border-bottom: 1px solid var(--collection-gold);
            color: rgba(255,255,255,.8);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .17em;
            text-transform: uppercase;
        }

        .collection-philosophy {
            padding: 60px 22px 72px;
            background: #0d0d0c;
        }

        .collection-philosophy-inner {
            width: min(650px, calc(100% - 44px));
        }

        .collection-philosophy h2 {
            color: var(--collection-ivory);
            font-size: 32px;
        }

        .collection-philosophy p {
            margin-top: 20px;
            color: rgba(255,255,255,.64);
            font-size: 16px;
            line-height: 1.58;
        }

        .public-page-visit {
            --visit-gold: #c7aa67;
            --home-gold: #b79a55;
            background: #070806;
        }

        .public-page-visit .site-header {
            min-height: 98px;
            padding: 14px clamp(34px, 6.2vw, 106px);
            background: linear-gradient(90deg, #17140f 0%, #202120 46%, #201f1d 100%);
        }

        .public-page-visit .brand-logo {
            width: 132px;
        }

        .public-page-visit .nav {
            margin-left: auto;
            margin-right: auto;
            gap: 28px;
            font-size: 10px;
            letter-spacing: .18em;
        }

        .visit-hero {
            height: clamp(340px, 26vw, 448px);
            min-height: 0;
            margin-top: 98px;
            place-items: end start;
        }

        .visit-hero img {
            opacity: .72;
        }

        .visit-hero::after {
            background: linear-gradient(180deg, rgba(0,0,0,.18), rgba(0,0,0,.55));
        }

        .visit-hero .image-hero-content {
            width: 100%;
            padding: 0 clamp(30px, 3.3vw, 56px) 42px;
            text-align: left;
        }

        .visit-hero h1 {
            color: #eee9df;
            font-size: clamp(3.4rem, 5vw, 5rem);
        }

        .visit-hero p {
            color: rgba(255,255,255,.72);
            font-size: 18px;
        }

        .visit-overview {
            padding: 38px 22px 44px;
            background: #0d0d0c;
            border-bottom: 1px solid var(--line);
        }

        .visit-overview .center-copy {
            width: min(820px, 100%);
        }

        .visit-overview h2 {
            color: #eee9df;
            font-size: 34px;
        }

        .visit-overview .center-copy > p {
            margin-top: 18px;
            color: rgba(255,255,255,.65);
            font-size: 16px;
            line-height: 1.55;
        }

        .visit-overview .visit-stats {
            width: min(680px, 100%);
            margin-top: 28px;
            gap: 52px;
        }

        .visit-overview .visit-stats h3 {
            color: var(--visit-gold);
            font-family: var(--public-font-sans);
            font-size: 12px;
        }

        .visit-overview .visit-stats p {
            font-size: 13px;
        }

        .visit-registration {
            padding: 48px 22px 72px;
            background: #070806;
        }

        .visit-registration .text-panel {
            width: min(890px, calc(100% - 44px));
        }

        .visit-registration .text-panel > h2 {
            color: #eee9df;
            font-size: 34px;
        }

        .visit-form {
            margin-top: 22px;
            gap: 18px;
        }

        .visit-form .form-grid {
            gap: 16px;
        }

        .visit-form .form-section-title {
            margin-top: 6px;
            padding-top: 22px;
            border-top: 1px solid var(--line);
            color: #eee9df;
            font-size: 21px;
        }

        .visit-form .form-section-title:first-of-type {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }

        .visit-form .field label {
            margin-bottom: 8px;
            color: rgba(255,255,255,.7);
            font-size: 10px;
            letter-spacing: .14em;
        }

        .visit-form .field input,
        .visit-form .field select {
            min-height: 52px;
            background: #151514;
            font-size: 14px;
        }

        .visit-form .field textarea {
            min-height: 130px;
            background: #151514;
            font-size: 14px;
        }

        .visit-field-note,
        .visit-submit-note {
            display: block;
            margin-top: 7px;
            color: rgba(255,255,255,.48);
            font-size: 10px;
            line-height: 1.45;
        }

        .visit-form .checkbox-list {
            gap: 14px;
        }

        .visit-form .checkbox-list label {
            font-size: 12px;
        }

        .visit-form .checkbox-list label:last-child {
            margin-top: 10px;
            padding-top: 22px;
            border-top: 1px solid var(--line);
        }

        .visit-form button {
            min-height: 54px;
            margin-top: 12px;
            background: var(--visit-gold);
            letter-spacing: .18em;
        }

        .visit-submit-note {
            margin-top: -8px;
            text-align: center;
        }

        .public-page-contact {
            --contact-gold: #b79a55;
            --home-gold: #b79a55;
            background: #070806;
        }

        .public-page-contact .site-header {
            min-height: 98px;
            padding: 14px clamp(34px, 6.2vw, 106px);
            background: linear-gradient(90deg, #17140f 0%, #202120 46%, #201f1d 100%);
        }

        .public-page-contact .brand-logo {
            width: 132px;
        }

        .public-page-contact .nav {
            margin-left: auto;
            margin-right: auto;
            gap: 28px;
            font-size: 10px;
            letter-spacing: .18em;
        }

        .contact-intro {
            margin-top: 98px;
            padding: 62px clamp(34px, 6.5vw, 105px) 58px;
            background: #070806;
            border-bottom: 1px solid var(--line);
        }

        .contact-intro-inner,
        .contact-main-inner {
            width: min(1290px, 100%);
            margin: 0 auto;
        }

        .contact-intro-inner {
            display: grid;
            grid-template-columns: 38% 62%;
            align-items: end;
            min-height: 180px;
        }

        .contact-overline {
            color: var(--contact-gold);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .contact-intro h1 {
            color: #eee9df;
            font-size: clamp(4rem, 6vw, 6.4rem);
            line-height: .95;
        }

        .contact-intro p {
            max-width: 580px;
            margin-top: 24px;
            color: rgba(255,255,255,.67);
            font-size: 20px;
            line-height: 1.5;
        }

        .contact-main {
            padding: 60px clamp(34px, 6.5vw, 105px);
            background: #070806;
        }

        .contact-main-inner {
            display: grid;
            grid-template-columns: 40% 60%;
            border-block: 1px solid var(--line);
        }

        .contact-enquiry {
            display: flex;
            min-height: 680px;
            padding: 38px 46px 38px 0;
            flex-direction: column;
            justify-content: space-between;
        }

        .contact-enquiry h2,
        .contact-direct h2 {
            margin-top: 18px;
            color: #eee9df;
            font-size: clamp(2.6rem, 3.5vw, 3.8rem);
            line-height: 1;
        }

        .contact-enquiry-copy {
            max-width: 430px;
            margin-top: 28px;
            color: rgba(255,255,255,.65);
            font-size: 17px;
            line-height: 1.65;
        }

        .contact-visit-link {
            display: inline-block;
            margin-top: 26px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--contact-gold);
            color: var(--contact-gold);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .contact-hours {
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .contact-hours p {
            margin-top: 20px;
            color: rgba(255,255,255,.65);
            font-size: 15px;
            line-height: 1.8;
        }

        .contact-direct {
            min-height: 680px;
            padding: 38px 0 38px 46px;
            border-left: 1px solid var(--line);
        }

        .contact-direct > p {
            margin-top: 20px;
            color: rgba(255,255,255,.62);
            font-size: 15px;
        }

        .contact-form {
            grid-template-columns: 1fr 1fr;
            gap: 22px 18px;
            margin-top: 28px;
        }

        .contact-form .form-response,
        .contact-form .field:nth-of-type(3),
        .contact-form .field:nth-of-type(4),
        .contact-form .contact-form-actions {
            grid-column: 1 / -1;
        }

        .contact-form .field label {
            color: rgba(255,255,255,.72);
            font-size: 10px;
            letter-spacing: .16em;
        }

        .contact-form .field input {
            min-height: 52px;
            background: #151514;
        }

        .contact-form .field textarea {
            min-height: 160px;
            background: #151514;
        }

        .contact-form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
        }

        .contact-form-actions p {
            color: rgba(255,255,255,.55);
            font-size: 12px;
        }

        .contact-form-actions button {
            width: auto;
            min-width: 170px;
            min-height: 52px;
            margin-left: auto;
            background: var(--contact-gold);
            letter-spacing: .16em;
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

        .experience h2{
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-weight: 500;
            font-size: 3.25rem !important;
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
            grid-template-columns: minmax(340px, 1.15fr) minmax(300px, .75fr) minmax(360px, .9fr);
            gap: clamp(50px, 6vw, 50px);
            min-height: 384px;
            padding: 54px clamp(44px, 6.1vw, 104px) 44px;
            background: #080907;
            border-top: 1px solid var(--line);
            font-family: "Inter", Arial, sans-serif;
        }

        .footer p,
        .footer a {
            color: var(--muted);
            font-size: 16px;
            line-height: 1.75;
        }

        .footer h3 {
            margin-bottom: 20px;
            color: var(--gold-soft);
            font-family: var(--public-font-sans);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .footer-brand .brand {
            min-width: 0;
        }

        .footer-brand .brand-logo {
            width: 170px;
        }

        .footer-brand > p {
            max-width: 400px;
            margin-top: 48px;
        }

        .footer-explore-links {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4px 52px;
        }

        .footer-links {
            display: grid;
            align-content: start;
            gap: 7px;
        }

        .footer-correspondence {
            min-height: 215px;
            padding-left: 40px;
            border-left: 1px solid var(--line);
        }

        .footer-correspondence > p {
            max-width: 370px;
        }

        .footer-contact-link {
            display: inline-flex;
            align-items: center;
            gap: 18px;
            margin-top: 22px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255,255,255,.7);
            color: rgba(255,255,255,.82) !important;
            font-size: 10px !important;
            font-weight: 700;
            letter-spacing: .18em;
            line-height: 1 !important;
            text-transform: uppercase;
        }

        .socials {
            display: flex;
            gap: 10px;
            margin-top: 28px;
        }

        .socials a {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border: 1px solid var(--line);
            color: rgba(255,255,255,.8);
        }

        .socials svg {
            width: 15px;
            height: 15px;
        }

        .copyright {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-self: end;
            padding-top: 18px;
            border-top: 1px solid var(--line);
        }

        .copyright p {
            font-size: 10px;
            letter-spacing: .2em;
            text-transform: uppercase;
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

        .hero .hero-media,
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
                padding: 14px 20px;
            }

            .hero {
                min-height: 520px;
            }

            .nav {
                position: fixed;
                z-index: -1;
                top: 76px;
                right: 0;
                left: 0;
                display: flex;
                align-items: stretch;
                flex-direction: column;
                gap: 0;
                max-height: calc(100dvh - 76px);
                margin: 0;
                padding: 18px 24px 30px;
                overflow-y: auto;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                background: rgba(13, 13, 12, 0.98);
                box-shadow: 0 22px 45px rgba(0, 0, 0, 0.45);
                opacity: 0;
                visibility: hidden;
                transform: translateY(-12px);
                transition: opacity 180ms ease, transform 220ms ease, visibility 220ms;
                pointer-events: none;
            }

            .nav a {
                display: flex;
                align-items: center;
                min-height: 58px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.09);
                color: rgba(255, 255, 255, 0.78);
                font-size: 12px;
                letter-spacing: 0.18em;
            }

            .nav a::after {
                display: none;
            }

            .nav-toggle {
                position: absolute;
                z-index: 2;
                right: 16px;
                display: block;
            }

            .nav-toggle[aria-expanded="true"] span:nth-child(1) {
                transform: translateY(7px) rotate(45deg);
            }

            .nav-toggle[aria-expanded="true"] span:nth-child(2) {
                opacity: 0;
            }

            .nav-toggle[aria-expanded="true"] span:nth-child(3) {
                transform: translateY(-7px) rotate(-45deg);
            }

            body.mobile-nav-open {
                overflow: hidden;
            }

            body.mobile-nav-open .nav {
                z-index: 21;
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
                pointer-events: auto;
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

            .footer-correspondence {
                min-height: 0;
                padding-left: 0;
                border-left: 0;
            }

            .home-access-link {
                display: none;
            }

            .public-page-home .hero {
                margin-top: 76px;
            }

            .public-page-about .image-hero {
                min-height: 440px;
                margin-top: 76px;
            }

            .programme-hero {
                grid-template-columns: 1fr;
                height: auto;
                margin-top: 76px;
            }

            .programme-hero-image {
                width: 100%;
                height: 440px;
                margin-left: 0;
                min-height: 440px;
                -webkit-mask-image: none;
                mask-image: none;
            }

            .programme-list-heading,
            .programme-editorial-inner,
            .programme-research-inner {
                grid-template-columns: 1fr;
            }

            .programme-research-inner {
                gap: 40px;
            }

            .programme-editorial-inner {
                height: auto;
            }

            .programme-editorial-copy,
            .programme-editorial-image {
                width: 100%;
            }

            .programme-editorial-image {
                height: auto;
                min-height: 360px;
                padding-inline: 24px;
                aspect-ratio: 4 / 3;
            }

            .programme-row {
                grid-template-columns: 48px 1fr 130px;
                padding: 28px 0;
            }

            .programme-row-description {
                grid-column: 2 / 3;
            }

            .programme-row img {
                grid-column: 3;
                grid-row: 1 / span 2;
                width: 130px;
                height: 96px;
            }

            .programme-coming-grid {
                grid-template-columns: 1fr;
            }

            .programme-coming-card + .programme-coming-card {
                border-top: 1px solid var(--line);
                border-left: 0;
            }

            .collection-index-intro {
                margin-top: 76px;
            }

            .collection-index-heading,
            .collection-reading-inner {
                grid-template-columns: 1fr;
            }

            .collection-reading-divider {
                display: none;
            }

            .visit-hero {
                height: 420px;
                margin-top: 76px;
            }

            .contact-intro {
                margin-top: 76px;
            }

            .contact-intro-inner,
            .contact-main-inner {
                grid-template-columns: 1fr;
            }

            .contact-intro-inner {
                gap: 28px;
            }

            .contact-enquiry,
            .contact-direct {
                min-height: 0;
                padding: 42px 0;
            }

            .contact-direct {
                border-top: 1px solid var(--line);
                border-left: 0;
            }

            .contact-hours {
                margin-top: 70px;
            }

            .public-page-home .hero-content {
                top: 0;
            }

            .public-page-home .site-header,
            .public-page-about .site-header,
            .public-page-events .site-header,
            .public-page-collection .site-header,
            .public-page-visit .site-header,
            .public-page-contact .site-header {
                justify-content: center;
                min-height: 76px;
                padding: 14px 20px;
            }

            .public-page-home .brand,
            .public-page-about .brand,
            .public-page-events .brand,
            .public-page-collection .brand,
            .public-page-visit .brand,
            .public-page-contact .brand {
                min-width: 0;
            }

            .public-page-home .brand-logo,
            .public-page-about .brand-logo,
            .public-page-events .brand-logo,
            .public-page-collection .brand-logo,
            .public-page-visit .brand-logo,
            .public-page-contact .brand-logo {
                width: 150px;
            }

            .home-story-inner {
                grid-template-columns: 1fr;
            }

            .home-story-copy p {
                margin-top: 34px;
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

            .public-page-home .home-section,
            .home-story {
                padding-left: 18px;
                padding-right: 18px;
            }

            .public-page-home .hero h1 {
                font-size: clamp(2.5rem, 13vw, 4rem);
                letter-spacing: .08em;
            }

            .public-page-home .collection-frame {
                aspect-ratio: 5 / 6;
            }

            .public-page-home .home-section .section-head p {
                font-size: 14px;
            }

            .public-page-home .experience {
                min-height: 420px;
            }

            .public-page-home .experience p {
                font-size: 15px;
            }

            .programme-hero-copy,
            .programme-list,
            .programme-preparation,
            .programme-research {
                padding-left: 18px;
                padding-right: 18px;
            }

            .programme-hero {
                min-height: 0;
            }

            .programme-hero-copy {
                padding-top: 54px;
                padding-bottom: 54px;
            }

            .programme-hero h1 {
                font-size: clamp(3.8rem, 18vw, 5.8rem);
            }

            .programme-hero-summary,
            .programme-coming-grid {
                grid-template-columns: 1fr;
            }

            .programme-row {
                grid-template-columns: 38px 1fr;
                gap: 18px;
            }

            .programme-row-description {
                grid-column: 2;
            }

            .programme-row img {
                grid-column: 2;
                grid-row: auto;
                width: 100%;
                height: 180px;
            }

            .programme-editorial-copy {
                padding: 48px 18px;
            }

            .programme-editorial-image {
                min-height: 300px;
                padding-inline: 0;
                aspect-ratio: 1 / 1;
            }

            .programme-editorial-image h3 {
                right: 24px;
                bottom: 28px;
                left: 24px;
                font-size: clamp(2rem, 10vw, 2.8rem);
            }

            .programme-editorial-label {
                left: 24px;
                bottom: 84px;
            }

            .collection-index-intro,
            .collection-artist-index,
            .collection-reading {
                padding-left: 18px;
                padding-right: 18px;
            }

            .collection-artist-row {
                grid-template-columns: 38px 1fr;
                gap: 14px;
                padding: 18px 0;
            }

            .collection-artist-country {
                grid-column: 2;
            }

            .contact-intro,
            .contact-main {
                padding-left: 18px;
                padding-right: 18px;
            }

            .contact-form {
                grid-template-columns: 1fr;
            }

            .contact-form .field {
                grid-column: 1;
            }

            .contact-form-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .contact-form-actions button {
                width: 100%;
            }
        }
    </style>
</head>
<body class="public-page public-page-{{ $publicPage }}">
    <header class="site-header">
        <a class="brand" href="{{ $routes['home'] }}" aria-label="Museum Azman home">
            <img class="brand-logo" src="{{ asset('media/museum-azman-logo.svg') }}?v=2" alt="Museum Azman">
        </a>
        @php
            $mainNavigation = in_array($publicPage, ['home', 'about', 'events', 'collection', 'visit', 'contact'], true)
                ? ['about' => 'About', 'events' => 'Programmes', 'collection' => 'Collection', 'visit' => 'Visit', 'contact' => 'Contact']
                : ['about' => 'About', 'events' => 'Events', 'artists' => 'Artists', 'collection' => 'Collection', 'visit' => 'Visit', 'contact' => 'Contact'];
        @endphp
        <nav class="nav" id="main-navigation" aria-label="Main navigation">
            @foreach($mainNavigation as $key => $label)
                <a class="{{ $publicPage === $key ? 'active' : '' }}" href="{{ $routes[$key] }}">{{ $label }}</a>
            @endforeach
        </nav>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-navigation" aria-label="Open navigation menu">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>
        @if(in_array($publicPage, ['home', 'about', 'events', 'collection', 'visit', 'contact'], true))
            <a class="home-access-link" href="{{ $routes['visit'] }}">Request Access</a>
        @endif
    </header>

    <main>
        @if($publicPage === 'home')
            <section class="hero" aria-label="Museum Azman">
                <img class="hero-media" src="{{ $homeHeroPosterUrl ?: asset('media/museum-azman-hero.png') }}" alt="Museum Azman gallery interior" fetchpriority="high">
                <div class="hero-content">
                    <h1 style="font-family: &quot;Cormorant Garamond&quot;, Georgia, serif !important;">{{ $homeContent['public_home_hero_title'] ?: 'Museum Azman' }}</h1>
                    <p class="home-hero-copy">{{ $homeContent['public_home_hero_subtitle'] ?: 'A private contemporary art museum creating dialogue between East and West.' }}</p>
                    <span class="home-eyebrow">Currently open by invitation only</span>
                    <a class="home-primary-button" href="{{ $routes['visit'] }}">Request Private Viewing <span aria-hidden="true">→</span></a>
                </div>
            </section>

            <section class="home-section home-programmes">
                <div class="home-section-inner">
                    <div class="section-head">
                        <h2>{{ $homeContent['public_home_events_title'] ?: 'Museum Programmes' }}</h2><p>{{ $homeContent['public_home_events_description'] ?: 'Tours, collection stories and cultural dialogue' }}</p>
                    </div>
                    <div class="grid three">
                        @foreach(range(0, 2) as $slot)
                            @php
                                $event = $homeFeaturedEvents->get($slot);
                                $programmeNumber = $slot + 1;
                                $programmePrefix = "public_home_programme_{$programmeNumber}_";
                                $useCustomProgramme = $homeContent[$programmePrefix.'source'] === 'custom';
                                $programmeDefaults = [
                                    ['image' => 'media/museum-programme-1.jpg', 'label' => 'By appointment', 'title' => 'Museum Tours', 'description' => 'A slow encounter with the permanent collection'],
                                    ['image' => 'media/museum-programme-2.jpg', 'label' => 'By appointment', 'title' => 'Private & Special Visits', 'description' => 'Tailored encounters with the collection'],
                                    ['image' => 'media/museum-programme-3.jpg', 'label' => 'For small groups', 'title' => 'Education Programmes', 'description' => 'Learning through close looking and exchange'],
                                ][$slot];
                                $programmeImage = $useCustomProgramme
                                    ? ($homeCustomProgrammeImageUrls[$programmeNumber] ?? null)
                                    : $event?->image_url;
                                $programmeImage ??= asset($programmeDefaults['image']);
                                $programmeLabel = $useCustomProgramme
                                    ? ($homeContent[$programmePrefix.'label'] ?: $programmeDefaults['label'])
                                    : ($event?->schedule ?: $programmeDefaults['label']);
                                $programmeTitle = $useCustomProgramme
                                    ? ($homeContent[$programmePrefix.'title'] ?: $programmeDefaults['title'])
                                    : ($event?->title ?: $programmeDefaults['title']);
                                $programmeDescription = $useCustomProgramme
                                    ? ($homeContent[$programmePrefix.'description'] ?: $programmeDefaults['description'])
                                    : ($event?->description ?: $programmeDefaults['description']);
                                $programmeLink = $useCustomProgramme && $homeContent[$programmePrefix.'link']
                                    ? $homeContent[$programmePrefix.'link']
                                    : $routes['events'];
                            @endphp
                            <a class="card home-card" href="{{ $programmeLink }}">
                                <img src="{{ $programmeImage }}" alt="{{ $programmeTitle }}" loading="lazy">
                                <span class="home-card-overline">{{ $programmeLabel }}</span>
                                <h3>{{ $programmeTitle }}</h3>
                                <p>{{ $programmeDescription }}</p>
                            </a>
                        @endforeach
                    </div>
                    <a class="section-link" href="{{ $routes['events'] }}">Explore programmes &nbsp; →</a>
                </div>
            </section>

            <section class="home-section home-collection-focus">
                <div class="home-section-inner">
                    <div class="section-head">
                        <h2>{{ $homeContent['public_home_works_title'] ?: 'Collection in Focus' }}</h2><p>{{ $homeContent['public_home_works_description'] ?: 'Selected works and artists from the permanent collection' }}</p>
                    </div>
                    <div class="grid three">
                        @foreach(range(0, 2) as $slot)
                            @php
                                $item = $homeSelectedWorks->get($slot);
                                $featuredArtwork = $item?->artwork;
                                $collectionNumber = $slot + 1;
                                $collectionPrefix = "public_home_collection_{$collectionNumber}_";
                                $useCustomCollection = $homeContent[$collectionPrefix.'source'] === 'custom';
                                $collectionTitle = $useCustomCollection
                                    ? ($homeContent[$collectionPrefix.'title'] ?: ['Walhalla', 'Material (SC) I', 'Untitled'][$slot])
                                    : ($featuredArtwork?->title ?: ['Walhalla', 'Material (SC) I', 'Untitled'][$slot]);
                                $collectionArtist = $useCustomCollection
                                    ? ($homeContent[$collectionPrefix.'artist'] ?: $artistFor($slot))
                                    : ($featuredArtwork?->artist?->name ?: $artistFor($slot));
                                $collectionYear = $useCustomCollection
                                    ? ($homeContent[$collectionPrefix.'year'] ?: $yearFor($slot, '2025'))
                                    : ($featuredArtwork?->year ?: $yearFor($slot, '2025'));
                                $collectionMedium = $useCustomCollection
                                    ? ($homeContent[$collectionPrefix.'medium'] ?: $mediumFor($slot, 'Mixed media'))
                                    : ($featuredArtwork?->medium ?: $mediumFor($slot, 'Mixed media'));
                                $collectionImage = $useCustomCollection
                                    ? ($homeCustomCollectionImageUrls[$collectionNumber] ?? null)
                                    : $featuredArtwork?->primary_image_url;
                                $collectionImage ??= $imageFor($slot);
                                $collectionLink = $useCustomCollection && $homeContent[$collectionPrefix.'link']
                                    ? $homeContent[$collectionPrefix.'link']
                                    : $routes['collection'];
                            @endphp
                            <a class="card home-card collection-card" href="{{ $collectionLink }}">
                                <div class="collection-frame"><img src="{{ $collectionImage }}" alt="{{ $collectionTitle }}" loading="lazy"></div>
                                <div class="collection-meta"><div><h3>{{ $collectionTitle }}</h3><p>{{ $collectionArtist }}</p></div><span class="home-card-overline">{{ $collectionYear }}</span></div>
                                <small>{{ $collectionMedium }}</small>
                            </a>
                        @endforeach
                    </div>
                    <a class="section-link" href="{{ $routes['collection'] }}">Explore the collection &nbsp; →</a>
                </div>
            </section>

            <section class="experience">
                <div class="experience-inner">
                    <h2>{{ $homeContent['public_home_experience_title'] }}</h2><p>{{ $homeContent['public_home_experience_description'] }}</p><a class="home-outline-button" href="{{ $routes['visit'] }}">{{ $homeContent['public_home_experience_button'] }} <span aria-hidden="true">→</span></a>
                </div>
            </section>

            @php
                $storyArtwork = $homeStoryWork?->artwork ?: $homeSelectedWorks->first()?->artwork;
                $storyArtist = $storyArtwork?->artist;
                $storyUsesCustomImage = $homeContent['public_home_story_source'] === 'custom' && $homeStoryImageUrl;
                $storyImageUrl = $storyUsesCustomImage ? $homeStoryImageUrl : ($storyArtwork?->primary_image_url ?: $imageFor(3));
                $storyTitle = $homeContent['public_home_story_title'] ?: $storyArtwork?->title ?: 'Landscapes of the Mind.';
                $storyDescription = $homeContent['public_home_story_description']
                    ?: (($storyArtist?->name ?: 'An artist from the Museum Azman collection')
                        .($storyArtwork?->year ? ' ('.$storyArtwork->year.')' : '')
                        .' invites a slower encounter with material, memory, place and the shifting perspectives held within contemporary art.');
            @endphp
            <section class="home-story">
                <div class="home-story-inner">
                    <div class="home-story-image">
                        <img src="{{ $storyImageUrl }}" alt="{{ $storyUsesCustomImage ? ($storyTitle ?: 'Story image') : ($storyArtwork?->title ?: 'Collection highlight') }}" loading="lazy">
                        <span class="home-story-label">Collection highlight / 01</span>
                    </div>
                    <div class="home-story-copy">
                        <small>{{ $homeContent['public_home_story_eyebrow'] }}</small>
                        <h2>{{ $storyTitle }}</h2>
                        <p>{{ $storyDescription }}</p>
                        <a class="home-text-link" href="{{ $routes['collection'] }}">{{ $homeContent['public_home_story_button'] }} &nbsp; →</a>
                    </div>
                </div>
            </section>

            <section class="vision">
                <div class="vision-inner">
                    <h2>{{ $homeContent['public_home_vision_title'] }}</h2><p>{{ $homeContent['public_home_vision_paragraph_1'] }}</p><p>{{ $homeContent['public_home_vision_paragraph_2'] }}</p><p class="note">{{ $homeContent['public_home_vision_note'] }}</p>
                </div>
            </section>

            <section class="home-connect">
                <h2>{{ $homeContent['public_home_connect_title'] }}</h2>
                <p>{{ $homeContent['public_home_connect_description'] }}</p>
                <a class="home-primary-button" href="{{ $routes['visit'] }}">{{ $homeContent['public_home_connect_button'] }} <span aria-hidden="true">→</span></a>
            </section>
        @elseif($publicPage === 'events')
            @php
                $programmeEvents = collect(['currently_active', 'upcoming', 'archive'])
                    ->flatMap(fn ($section) => collect($publicEvents->get($section, [])))
                    ->take(3)
                    ->values();
                $programmeDefaults = [
                    ['title' => 'Museum Tours', 'label' => 'A slow encounter', 'description' => "A considered introduction to the museum's permanent collection, led by a member of the curatorial team.", 'image' => 'media/museum-programme-1.jpg'],
                    ['title' => 'Private & Special Visits', 'label' => 'By appointment', 'description' => 'Tailored visits for collectors, researchers, patrons and small groups, designed around your particular interests.', 'image' => 'media/museum-programme-2.jpg'],
                    ['title' => 'Education Programmes', 'label' => 'Learning through looking', 'description' => 'Small-format sessions for students and curious minds, using the collection as a prompt for close looking and exchange.', 'image' => 'media/museum-programme-3.jpg'],
                ];
                $programmeStoryArtwork = $artworks->get(3) ?: $artworks->first();
                $programmePageTitle = $eventContent['public_events_page_title'];
                $programmePageDescription = $eventContent['public_events_page_description'];
                $programmePreparationTitle = $eventContent['public_events_programming_title'];
                $comingTitles = [
                    $eventContent['public_events_program_1_title'],
                    $eventContent['public_events_program_2_title'],
                ];
                $comingDescriptions = [
                    $eventContent['public_events_program_1_description'],
                    $eventContent['public_events_program_2_description'],
                ];
                $comingLabels = [
                    $eventContent['public_events_program_1_label'],
                    $eventContent['public_events_program_2_label'],
                ];
            @endphp

            <section class="programme-hero">
                <div class="programme-hero-copy">
                    <span class="programme-kicker">{{ $eventContent['public_events_hero_kicker'] }}</span>
                    <h1>
                        @if($programmePageTitle === "Programmes\n& stories")
                            Programmes<br><em>&amp; stories</em>
                        @else
                            {{ $programmePageTitle }}
                        @endif
                    </h1>
                    <div class="programme-hero-summary">
                        <span class="programme-overline">{!! nl2br(e($eventContent['public_events_hero_label'])) !!}</span>
                        <p>{{ $programmePageDescription }}</p>
                    </div>
                </div>
                <div class="programme-hero-image">
                    <img src="{{ $eventsHeroImageUrl ?: $imageFor(6) }}" alt="{{ $programmePageTitle }}" fetchpriority="high">
                </div>
            </section>

            <section class="programme-list">
                <div class="programme-list-inner">
                    <div class="programme-list-heading">
                        <div><span class="programme-overline">{{ $eventContent['public_events_list_eyebrow'] }}</span><h2>{{ $eventContent['public_events_list_title'] }}</h2></div>
                        <p>{{ $eventContent['public_events_list_description'] }}</p>
                    </div>
                    @foreach(range(0, 2) as $slot)
                        @php $event = $programmeEvents->get($slot); $fallback = $programmeDefaults[$slot]; @endphp
                        <article class="programme-row">
                            <span class="programme-number">0{{ $slot + 1 }}</span>
                            <div>
                                <span class="programme-overline">{{ $event?->schedule ?: $fallback['label'] }}</span>
                                <h3>{{ $event?->title ?: $fallback['title'] }}</h3>
                            </div>
                            <p class="programme-row-description">{{ $event?->description ?: $fallback['description'] }}</p>
                            <img src="{{ $event?->image_url ?: asset($fallback['image']) }}" alt="{{ $event?->title ?: $fallback['title'] }}" loading="lazy">
                        </article>
                    @endforeach
                    <a class="programme-action" style="font-family: Arial, Helvetica, sans-serif;border-bottom: 1px solid var(--programme-gold);" href="{{ $routes['visit'] }}">{{ $eventContent['public_events_list_button'] }} &nbsp; →</a>
                </div>
            </section>

            <section class="programme-editorial">
                <div class="programme-editorial-inner">
                    <div class="programme-editorial-copy">
                        <span class="programme-overline">{{ $eventContent['public_events_story_eyebrow'] }}</span>
                        <h2>{{ $eventContent['public_events_story_title'] }}</h2>
                        <p>{{ $eventContent['public_events_story_description'] }}</p>
                        <a class="programme-action" href="{{ $routes['collection'] }}">
                            <span>{{ $eventContent['public_events_story_button'] }}</span>
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>
                    <div class="programme-editorial-image">
                        <img src="{{ $eventsStoryImageUrl ?: $programmeStoryArtwork?->primary_image_url ?: $imageFor(3) }}" alt="{{ $eventContent['public_events_story_caption'] }}" loading="lazy">
                        <span class="programme-editorial-label">Collection highlight / 01</span>
                        <h3>{{ $eventContent['public_events_story_caption'] }}</h3>
                    </div>
                </div>
            </section>

            <section class="programme-preparation">
                <div class="programme-preparation-inner">
                    <div class="programme-preparation-intro">
                        <span class="programme-overline">In preparation</span>
                        <h2>{{ $programmePreparationTitle }}</h2>
                        <p>{{ $eventContent['public_events_programming_description'] }}</p>
                    </div>
                    <div class="programme-coming-grid">
                        <article class="programme-coming-card">
                            <span class="programme-coming-badge">Coming soon</span>
                            <svg class="programme-coming-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 2l1.7 5.3L19 9l-5.3 1.7L12 16l-1.7-5.3L5 9l5.3-1.7L12 2Z"></path><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15Z"></path></svg>
                            <span class="programme-overline">{{ $comingLabels[0] }}</span>
                            <h3>{{ $comingTitles[0] }}</h3>
                            <p>{{ $comingDescriptions[0] }}</p>
                        </article>
                        <article class="programme-coming-card">
                            <span class="programme-coming-badge">Coming soon</span>
                            <svg class="programme-coming-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 14v-2a8 8 0 0 1 16 0v2"></path><path d="M4 14h3v7H5.5A1.5 1.5 0 0 1 4 19.5V14Zm16 0h-3v7h1.5a1.5 1.5 0 0 0 1.5-1.5V14Z"></path></svg>
                            <span class="programme-overline">{{ $comingLabels[1] }}</span>
                            <h3>{{ $comingTitles[1] }}</h3>
                            <p>{{ $comingDescriptions[1] }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="programme-research">
                @php $researchTitleLines = preg_split('/\R/', $eventContent['public_events_research_title'], 2); @endphp
                <div class="programme-research-inner">
                    <div>
                        <span class="programme-overline">{{ $eventContent['public_events_research_eyebrow'] }}</span>
                        <h2>
                            {{ $researchTitleLines[0] ?? '' }}
                            @if(isset($researchTitleLines[1]))<br><em>{{ $researchTitleLines[1] }}</em>@endif
                        </h2>
                    </div>
                    <div>
                        <svg class="programme-research-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path d="M3.5 5.5A3.5 3.5 0 0 1 7 2h4v17H7a3.5 3.5 0 0 0-3.5 3V5.5Z"></path>
                            <path d="M20.5 5.5A3.5 3.5 0 0 0 17 2h-4v17h4a3.5 3.5 0 0 1 3.5 3V5.5Z"></path>
                        </svg>
                        <p>{{ $eventContent['public_events_research_description'] }}</p>
                        <a class="programme-research-button" href="{{ $routes['contact'] }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="1.5"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                            <span>{{ $eventContent['public_events_research_button'] }}</span>
                        </a>
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
            @php
                $collectionArtists = ($collectionCmsConfigured ? $publicCollectionItems->pluck('artwork.artist') : $artworks->pluck('artist'))
                    ->filter()
                    ->unique('id')
                    ->take(10)
                    ->values();
            @endphp

            <section class="collection-index-intro">
                <div class="collection-index-intro-inner">
                    <h1>{{ $collectionContent['public_collection_page_title'] }}</h1>
                    <p>{{ $collectionContent['public_collection_page_description'] }}</p>
                </div>
            </section>

            <section class="collection-artist-index">
                <div class="collection-artist-index-inner">
                    <div class="collection-index-heading">
                        <div><span class="collection-overline">{{ $collectionContent['public_collection_artists_eyebrow'] }}</span><h2>{{ $collectionContent['public_collection_artists_title'] }}</h2></div>
                        <p>{{ $collectionContent['public_collection_artists_note'] }}</p>
                    </div>
                    @forelse($collectionArtists as $index => $artist)
                        <article class="collection-artist-row {{ strcasecmp((string) $artist->country, 'Malaysia') === 0 ? 'is-highlighted' : '' }}">
                            <span class="collection-artist-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $artist->name }}</h3>
                            <span class="collection-artist-country">{{ $artist->country ?: 'International' }}</span>
                        </article>
                    @empty
                        <p class="page-copy">Selected artists coming soon.</p>
                    @endforelse
                </div>
            </section>

            <section class="collection-reading">
                <div class="collection-reading-inner">
                    <div><span class="collection-overline">{{ $collectionContent['public_collection_story_eyebrow'] }}</span><h2>{!! nl2br(e($collectionContent['public_collection_story_title'])) !!}</h2></div>
                    <span class="collection-reading-divider" aria-hidden="true"></span>
                    <div><p>{{ $collectionContent['public_collection_story_description'] }}</p><a href="{{ $routes['events'] }}">{{ $collectionContent['public_collection_story_button'] }} &nbsp; ↗</a></div>
                </div>
            </section>

            <section class="collection-philosophy">
                <div class="collection-philosophy-inner">
                    <h2>{{ $collectionContent['public_collection_philosophy_title'] }}</h2>
                    <p>{{ $collectionContent['public_collection_philosophy_paragraph_1'] }}</p>
                    <p>{{ $collectionContent['public_collection_philosophy_paragraph_2'] }}</p>
                    <p>{{ $collectionContent['public_collection_philosophy_paragraph_3'] }}</p>
                </div>
            </section>
        @elseif($publicPage === 'visit')
            <section class="image-hero visit-hero">
                <img src="{{ $imageFor(10) }}" alt="Museum Azman private viewing" loading="eager">
                <div class="image-hero-content">
                    <h1>Request a Visit</h1>
                    <p>Experience the collection through a private, considered encounter</p>
                </div>
            </section>

            <section class="visit-overview">
                <div class="center-copy">
                    <h2>Private Viewings</h2>
                    <p>Museum Azman offers private, collection-led viewings for collectors, curators, researchers and art enthusiasts. Complete the form below to request an encounter shaped around your interests. All requests are reviewed individually by our team.</p>
                </div>
                <div class="visit-stats">
                    <div><h3>Duration</h3><p>90–120 minutes</p></div>
                    <div><h3>Availability</h3><p>Tuesday - Saturday</p></div>
                    <div><h3>Group Size</h3><p>Group size considered individually</p></div>
                </div>
            </section>

            <section class="visit-registration">
                <div class="text-panel">
                    <h2>Visitor Registration</h2>
                    <form class="public-form visit-form" method="POST" action="{{ route('public.visit.store', [], false) }}">
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
                            <div class="field"><label for="visit-guests">Number of Guests *</label><input id="visit-guests" name="guests" type="number" min="1" max="6" value="{{ old('guests', 1) }}" required><small class="visit-field-note">Group size is considered individually according to the nature of your request, preferred format and availability.</small></div>
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
                        <small class="visit-submit-note">All requests are reviewed individually. We will contact you within 48 hours to confirm your visit details.</small>
                    </form>
                </div>
            </section>
        @elseif($publicPage === 'contact')
            <section class="contact-intro">
                <div class="contact-intro-inner">
                    <span class="contact-overline">Museum Azman / Correspondence</span>
                    <div>
                        <h1>Contact</h1>
                        <p>We welcome inquiries from collectors, curators, artists, and art enthusiasts.</p>
                    </div>
                </div>
            </section>

            <section class="contact-main">
                <div class="contact-main-inner">
                    <div class="contact-enquiry">
                        <div>
                            <span class="contact-overline">Enquiries</span>
                            <h2>Get in touch.</h2>
                            <p class="contact-enquiry-copy">For private viewing requests, please use our visitor registration form. For other enquiries, use the form and a member of the museum team will respond personally.</p>
                            <a class="contact-visit-link" href="{{ $routes['visit'] }}">Request a private viewing</a>
                        </div>
                        <div class="contact-hours">
                            <span class="contact-overline">Visiting hours</span>
                            <p>By appointment only<br>Tuesday - Saturday<br>10:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                    <div class="contact-direct">
                        <span class="contact-overline">Direct enquiry</span>
                        <h2>Send a message.</h2>
                        <p>Please share a little context so the appropriate member of the museum team can respond.</p>
                        <form class="public-form contact-form" method="POST" action="{{ route('public.contact.store', [], false) }}">
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
                            <div class="contact-form-actions"><p>We will respond personally as soon as possible.</p><button type="submit">Send Message</button></div>
                        </form>
                    </div>
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

            <section class="section about-values">
                <div class="center-copy">
                    <h2>{{ $aboutContent['public_about_values_title'] }}</h2>
                    <div class="value-grid">
                        @foreach(range(1, 3) as $number)<div><h3>{{ $aboutContent['public_about_value_'.$number.'_title'] }}</h3><p>{{ $aboutContent['public_about_value_'.$number.'_description'] }}</p></div>@endforeach
                    </div>
                </div>
            </section>

            <section class="section alt about-space">
                <div class="space-grid" style='font-family: "Inter", Arial, sans-serif;'>
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
        <div class="footer-brand">
            <a class="brand" href="{{ $routes['home'] }}" aria-label="Museum Azman home">
                <img class="brand-logo" src="{{ asset('media/museum-azman-logo.svg') }}?v=2" alt="Museum Azman">
            </a>
            <p>A private contemporary art museum creating dialogue between East and West through a living collection.</p>
        </div>
        <div>
            <h3>Explore</h3>
            <div class="footer-explore-links">
                <div class="footer-links">
                    <a href="{{ $routes['about'] }}">About</a>
                    <a href="{{ $routes['collection'] }}">Collection</a>
                    <a href="{{ $routes['contact'] }}">Contact</a>
                </div>
                <div class="footer-links">
                    <a href="{{ $routes['events'] }}">Programmes</a>
                    <a href="{{ $routes['visit'] }}">Visit</a>
                </div>
            </div>
        </div>
        <div class="footer-correspondence">
            <h3>Correspondence</h3>
            <p>For enquiries, private correspondence and collection research, please contact the museum through our enquiry form.</p>
            <a class="footer-contact-link" href="{{ $routes['contact'] }}">Contact the Museum <span aria-hidden="true">↗</span></a>
            <div class="socials">
                <a href="{{ $routes['contact'] }}" aria-label="Instagram">
                    <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"></circle></svg>
                </a>
            </div>
        </div>
        <div class="copyright">
            <p>© {{ date('Y') }} Museum Azman. All rights reserved.</p>
            <p>Private Collection · By Appointment</p>
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
            const navToggle = document.querySelector('.nav-toggle');
            const mainNavigation = document.querySelector('.nav');
            const closeNavigation = () => {
                if (!navToggle || !mainNavigation) {
                    return;
                }

                document.body.classList.remove('mobile-nav-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Open navigation menu');
            };

            navToggle?.addEventListener('click', () => {
                const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
                if (isOpen) {
                    closeNavigation();
                    return;
                }

                document.body.classList.add('mobile-nav-open');
                navToggle.setAttribute('aria-expanded', 'true');
                navToggle.setAttribute('aria-label', 'Close navigation menu');
            });

            mainNavigation?.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', closeNavigation);
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 980) {
                    closeNavigation();
                }
            });

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
                if (event.key === 'Escape') {
                    if (lightbox?.classList.contains('is-open')) {
                        close();
                    }
                    if (document.body.classList.contains('mobile-nav-open')) {
                        closeNavigation();
                        navToggle?.focus();
                    }
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
