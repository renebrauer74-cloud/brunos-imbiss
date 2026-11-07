@props(['title' => 'Admin', 'active' => null, 'guest' => false])
@php
    $nav = [
        'menu' => ['admin.menu.index', 'Speisekarte'],
        'favorites' => ['admin.favorites.index', 'Lieblinge'],
        'images' => ['admin.images.index', 'Bilder'],
        'settings' => ['admin.settings.edit', 'Einstellungen'],
        'account' => ['admin.account.edit', 'Konto'],
    ];
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} – Bruno’s Imbiss Admin</title>
    <link rel="icon" href="{{ asset('assets/img/favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ filemtime(public_path('assets/css/admin.css')) }}">
</head>
<body @class(['admin', 'admin-guest' => $guest])>

@unless ($guest)
<header class="admin-header">
    <div class="admin-wrap admin-header-inner">
        <a class="admin-brand" href="{{ route('admin.menu.index') }}">Bruno’s <span>Admin</span></a>
        <div class="admin-header-actions">
            <a class="admin-link" href="{{ route('home') }}" target="_blank" rel="noopener">Website ansehen ↗</a>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="admin-link" type="submit">Abmelden</button>
            </form>
        </div>
    </div>
    <nav class="admin-nav" aria-label="Admin-Navigation">
        <div class="admin-wrap">
            @foreach ($nav as $key => [$route, $label])
                <a href="{{ route($route) }}" @class(['is-active' => $active === $key])>{{ $label }}</a>
            @endforeach
        </div>
    </nav>
</header>
@endunless

<main class="admin-main">
    <div class="admin-wrap">
        @if (request()->has('zu-gross'))
            <div class="alert alert-error" role="alert">Die Datei(en) waren zu groß für den Server. Bitte kleinere oder weniger Fotos auf einmal hochladen.</div>
        @endif
        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error" role="alert">{{ session('error') }}</div>
        @endif
        @foreach ($errors->all() as $error)
            <div class="alert alert-error" role="alert">{{ $error }}</div>
        @endforeach

        {{ $slot }}
    </div>
</main>
<script src="{{ asset('assets/js/admin.js') }}?v={{ filemtime(public_path('assets/js/admin.js')) }}" defer></script>
</body>
</html>
