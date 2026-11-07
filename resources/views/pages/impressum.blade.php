<x-layouts.site title="Impressum" :noindex="true">
    <main id="main" class="section legal">
        <div class="container legal-inner">
            <h1>Impressum</h1>
            @if (filled($settings->imprint))
                <div class="legal-text">{!! nl2br(e($settings->imprint), false) !!}</div>
            @else
                <p><strong>{{ $settings->site_title }}</strong><br>
                    @if (filled($settings->street)){{ $settings->street }}<br>@endif
                    {{ $settings->zip_city }}</p>
                @if (filled($settings->phone))
                    <p>Telefon: {{ $settings->phone }}</p>
                @endif
                @if (filled($settings->email))
                    <p>E-Mail: {{ $settings->email }}</p>
                @endif
                <p class="info-note">Die vollständigen Angaben gemäß § 5 DDG werden in Kürze ergänzt.</p>
            @endif
            <p><a class="btn btn-outline" href="{{ route('home') }}">← Zur Startseite</a></p>
        </div>
    </main>
</x-layouts.site>
