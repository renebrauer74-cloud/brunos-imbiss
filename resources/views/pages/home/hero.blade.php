@inject('describeOpeningStatus', 'App\Actions\Site\DescribeOpeningStatusAction')
@inject('contactLinks', 'App\Actions\Site\BuildContactLinksAction')
@php
    $status = $describeOpeningStatus->execute($settings);
    $tel = $contactLinks->execute($settings)['tel'];
@endphp
<section class="hero">
    <div class="container hero-inner">
        <h1 class="visually-hidden">{{ $settings->site_title }} – {{ $settings->tagline }}</h1>
        <img class="hero-logo"
             src="{{ asset('assets/img/logo.jpg') }}"
             alt="Logo Bruno’s Imbiss: Eine Wurst boxt Bruno – … immer frisch, immer lecker!"
             width="1314" height="1009" fetchpriority="high">

        <p class="hero-tagline">{{ $settings->tagline }}</p>

        @if ($status)
            <p @class(['status-pill', 'is-open' => $status['open'], 'is-closed' => ! $status['open']])>
                <span class="dot" aria-hidden="true"></span>{{ $status['text'] }}
            </p>
        @endif

        <div class="hero-actions">
            <a class="btn btn-red btn-large" href="#speisekarte">Zur Speisekarte</a>
            @if ($tel)
                <a class="btn btn-outline btn-large" href="{{ $tel }}">Anrufen</a>
            @else
                <a class="btn btn-outline btn-large" href="#anfahrt">So findest du uns</a>
            @endif
        </div>

        @include('pages.home.award')
    </div>
</section>

<div class="ticker" aria-hidden="true">
    <div class="ticker-track">
        @for ($i = 0; $i < 2; $i++)
            <span>Currywurst</span><span>Pommes</span><span>Schnitzel</span><span>Frikadellen</span><span>Kroketten</span><span>Hot Dog</span><span>Ruhrpott-Liebe</span>
        @endfor
    </div>
</div>
