<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="refresh" content="300">
    <title>Temporarily Unavailable | Museum Azman</title>
    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            display: grid;
            min-height: 100vh;
            place-items: center;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 18% 8%, rgba(200, 168, 93, .12), transparent 28rem),
                #050505;
            color: #f4f0e8;
            font-family: Arial, Helvetica, sans-serif;
        }
        .shell { width: min(100% - 40px, 920px); padding: 48px 0; text-align: center; }
        .logo { display: block; width: 210px; max-width: 58vw; margin: 0 auto 56px; }
        .eyebrow { color: #c8a85d; font-size: 12px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; }
        h1 { margin: 18px 0 20px; font: 400 clamp(42px, 8vw, 82px)/1.02 Georgia, "Times New Roman", serif; }
        .copy { max-width: 650px; margin: 0 auto; color: rgba(255,255,255,.66); font-size: clamp(16px, 2vw, 19px); line-height: 1.75; }
        .divider { width: 72px; height: 1px; margin: 36px auto; background: #c8a85d; }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.24);
            padding: 0 22px;
            color: #f4f0e8;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .06em;
            text-decoration: none;
            text-transform: uppercase;
            transition: 160ms ease;
        }
        .button:hover { border-color: #c8a85d; color: #e7d295; }
        .note { margin-top: 40px; color: rgba(255,255,255,.38); font-size: 12px; letter-spacing: .04em; }
    </style>
</head>
<body>
    <main class="shell">
        <img class="logo" src="{{ asset('media/museum-azman-logo.svg') }}?v=2" alt="Museum Azman">
        <p class="eyebrow">Scheduled Maintenance</p>
        <h1>We’ll be back shortly.</h1>
        <p class="copy">Museum Azman is temporarily unavailable while we carry out essential improvements. Thank you for your patience—please try again in a few minutes.</p>
        <div class="divider"></div>
        <div class="actions">
            <a class="button" href="{{ url()->current() }}">Try Again</a>
            <a class="button" href="mailto:faiz@museumazman.com">Contact Us</a>
        </div>
        <p class="note">This page will refresh automatically.</p>
    </main>
</body>
</html>
