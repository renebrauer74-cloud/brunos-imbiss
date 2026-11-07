@props(['title' => null, 'description' => null, 'noindex' => false, 'home' => false])
@inject('contactLinks', 'App\Actions\Site\BuildContactLinksAction')
@php
    $links = $contactLinks->execute($settings);
    $fullTitle = filled($title) ? $title.' – '.$settings->site_title : $settings->site_title.' – '.$settings->tagline;
    $description ??= trim($settings->tagline.'. '.$settings->slogan, '. ');
    $anchor = fn (string $id): string => $home ? '#'.$id : route('home').'#'.$id;
    $phoneIcon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/></svg>';
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#FFC21A">
    @if ($noindex)
        <meta name="robots" content="noindex">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="de_DE">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('assets/img/logo.jpg') }}">
    <link rel="icon" href="{{ asset('assets/img/favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo.jpg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    {{ $head ?? '' }}
</head>
<body>
<a class="skip-link" href="#main">Zum Inhalt springen</a>

@if (filled($settings->notice))
    <div class="notice-bar" role="status">{{ $settings->notice }}</div>
@endif

<header class="site-header" id="top">
    <div class="container header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="Startseite">
            <span class="brand-name">Bruno<span class="apos">’</span>s</span>
            <span class="brand-sub">Imbiss</span>
        </a>

        <button class="nav-toggle" aria-expanded="false" aria-controls="main-nav" aria-label="Menü öffnen">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
            <a href="{{ $anchor('speisekarte') }}">Speisekarte</a>
            @if ($galleryCount > 0)
                <a href="{{ route('gallery') }}">Galerie</a>
            @endif
            <a href="{{ $anchor('oeffnungszeiten') }}">Öffnungszeiten</a>
            <a href="{{ $anchor('anfahrt') }}">Anfahrt</a>
            @if ($links['tel'])
                <a class="btn btn-small btn-red nav-call" href="{{ $links['tel'] }}">{!! $phoneIcon !!} Anrufen</a>
            @endif
        </nav>
    </div>
</header>

{{ $slot }}

<footer class="site-footer">
    <div class="container footer-inner">
        <img class="footer-logo" src="{{ asset('assets/img/logo.jpg') }}" alt="" width="1314" height="1009" loading="lazy">
        <div class="footer-text">
            <p class="footer-slogan">{{ $settings->slogan }}</p>
            <p>{{ $settings->site_title }} · {{ $links['address'] }}</p>
            <nav class="footer-links" aria-label="Rechtliches">
                <a href="{{ route('imprint') }}">Impressum</a>
                <a href="{{ route('privacy') }}">Datenschutz</a>
            </nav>
            <p class="copyright">© {{ date('Y') }} {{ $settings->site_title }}</p>
        </div>
    </div>
</footer>

<nav class="action-bar" aria-label="Schnellzugriff">
    <a href="{{ $anchor('speisekarte') }}">
        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 4h16v2H4zm0 7h16v2H4zm0 7h16v2H4z"/></svg>Karte
    </a>
    <a href="{{ $links['maps'] }}" target="_blank" rel="noopener">
        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 5.2-7 13-7 13S5 14.2 5 9a7 7 0 0 1 7-7zm0 4.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/></svg>Route
    </a>
    @if ($links['tel'])
        <a class="is-primary" href="{{ $links['tel'] }}">{!! $phoneIcon !!}Anrufen</a>
    @else
        <a href="{{ $anchor('oeffnungszeiten') }}">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20zm0 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16zm1 3v4.6l3.4 3.4-1.4 1.4-4-4V7z"/></svg>Zeiten
        </a>
    @endif
</nav>

<dialog class="lightbox" id="lightbox" aria-label="Bildansicht">
    <form method="dialog"><button class="lightbox-close" aria-label="Schließen">×</button></form>
    <img src="" alt="">
    <p class="lightbox-title"></p>
    <button class="lightbox-nav lightbox-prev" type="button" aria-label="Vorheriges Bild" hidden>‹</button>
    <button class="lightbox-nav lightbox-next" type="button" aria-label="Nächstes Bild" hidden>›</button>
</dialog>

<script src="{{ asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}" defer></script>
</body>
</html>
