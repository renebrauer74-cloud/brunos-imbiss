@use('App\Enums\WeekdayEnum')
@inject('listOpeningHours', 'App\Actions\Site\ListOpeningHoursAction')
@inject('checkOpeningHours', 'App\Actions\Site\CheckOpeningHoursAction')
@inject('describeOpeningDay', 'App\Actions\Site\DescribeOpeningDayAction')
@inject('contactLinks', 'App\Actions\Site\BuildContactLinksAction')
@php
    $links = $contactLinks->execute($settings);
    $today = WeekdayEnum::fromDate(now());
@endphp
@if (filled($settings->about))
    <section class="section about" aria-labelledby="about-title">
        <div class="container about-inner">
            <div class="about-text">
                <p class="eyebrow">Bei Bruno</p>
                <h2 id="about-title">Imbiss mit Herz – mitten in Bochum</h2>
                <p>{!! nl2br(e($settings->about), false) !!}</p>
            </div>
        </div>
    </section>
@endif

<section class="section info" aria-label="Öffnungszeiten, Anfahrt und Kontakt">
    <div class="container info-grid">

        <article class="info-card" id="oeffnungszeiten">
            <h2><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20zm0 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16zm1 3v4.6l3.4 3.4-1.4 1.4-4-4V7z"/></svg>Öffnungszeiten</h2>
            @if ($checkOpeningHours->execute($settings))
                <table class="hours">
                    @foreach ($listOpeningHours->execute($settings) as $key => $day)
                        <tr @class(['is-today' => $key === $today->value, 'is-closed' => $day['closed']])>
                            <th scope="row">{{ WeekdayEnum::from($key)->label() }}</th>
                            <td>{{ $describeOpeningDay->execute($day) }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p>Unsere Öffnungszeiten folgen in Kürze.</p>
            @endif
            @if (filled($settings->hours_note))
                <p class="info-note">{!! nl2br(e($settings->hours_note), false) !!}</p>
            @endif
        </article>

        <article class="info-card" id="anfahrt">
            <h2><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 5.2-7 13-7 13S5 14.2 5 9a7 7 0 0 1 7-7zm0 4.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/></svg>So findest du uns</h2>
            <address>
                <strong>{{ $settings->site_title }}</strong><br>
                @if (filled($settings->street)){{ $settings->street }}<br>@endif
                {{ $settings->zip_city }}
            </address>
            <a class="btn btn-yellow" href="{{ $links['maps'] }}" target="_blank" rel="noopener">Route planen</a>
        </article>

        <article class="info-card" id="kontakt">
            <h2><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/></svg>Kontakt</h2>
            @if (blank($settings->phone) && blank($settings->email))
                <p>Komm einfach vorbei – wir freuen uns auf dich!</p>
            @endif
            @if ($links['tel'])
                <p class="contact-line"><span>Telefon</span><a href="{{ $links['tel'] }}">{{ $settings->phone }}</a></p>
            @endif
            @if (filled($settings->email))
                <p class="contact-line"><span>E-Mail</span><a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a></p>
            @endif
            @if ($links['tel'])
                <a class="btn btn-red" href="{{ $links['tel'] }}">Jetzt anrufen</a>
            @endif
        </article>

    </div>
</section>
