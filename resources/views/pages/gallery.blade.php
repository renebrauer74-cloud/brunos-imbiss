@use('App\Enums\MediaCollectionEnum')
@use('App\Enums\MediaConversionEnum')
<x-layouts.site title="Bildergalerie"
                :description="'Fotos aus '.$settings->site_title.' in '.$settings->zip_city.': Currywurst, Pommes, Schnitzel & mehr.'">
    <main id="main" class="section gallery-page">
        <div class="container">
            <header class="section-head">
                <p class="eyebrow">Frisch aus Brunos Küche</p>
                <h1>Bildergalerie</h1>
            </header>

            @if ($images->isEmpty())
                <p class="empty-hint">Hier gibt’s bald leckere Bilder zu sehen – schau später nochmal rein!</p>
            @else
                <div class="gallery-grid">
                    @foreach ($images as $image)
                        <button class="gallery-item js-lightbox" type="button" data-gallery="galerie"
                                data-src="{{ $image->getFirstMediaUrl(MediaCollectionEnum::Gallery->value, MediaConversionEnum::Web->value) }}"
                                data-title="{{ $image->caption }}"
                                aria-label="{{ filled($image->caption) ? $image->caption : 'Bild' }} vergrößern">
                            <img src="{{ $image->getFirstMediaUrl(MediaCollectionEnum::Gallery->value, MediaConversionEnum::Web->value) }}"
                                 alt="{{ $image->caption }}" loading="lazy">
                            @if (filled($image->caption))
                                <span>{{ $image->caption }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif

            <p class="photos-cta"><a class="btn btn-red btn-large" href="{{ route('home') }}#speisekarte">Zur Speisekarte</a></p>
        </div>
    </main>
</x-layouts.site>
