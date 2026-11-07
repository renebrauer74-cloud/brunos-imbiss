@use('App\Enums\MediaCollectionEnum')
@use('App\Enums\MediaConversionEnum')
<section class="section food-photos" id="fotos" aria-labelledby="photos-title">
    <div class="container">
        <header class="section-head">
            <p class="eyebrow">Frisch aus Brunos Küche</p>
            <h2 id="photos-title">Appetit bekommen?</h2>
        </header>

        <ul class="polaroids">
            @foreach ($homePhotos as $photo)
                @php($image = $photo->getFirstMediaUrl(MediaCollectionEnum::HomePhotos->value, MediaConversionEnum::Web->value))
                <li class="polaroid">
                    <figure>
                        @if ($image !== '')
                            <button class="polaroid-media js-lightbox" type="button" data-gallery="home"
                                    data-src="{{ $image }}" data-title="{{ $photo->caption }}"
                                    aria-label="Foto „{{ $photo->caption }}“ vergrößern">
                                <img src="{{ $image }}" alt="{{ $photo->caption }}" loading="lazy">
                            </button>
                        @else
                            <div class="polaroid-media polaroid-placeholder" aria-hidden="true">{{ $photo->slot->emoji() }}</div>
                        @endif
                        @if (filled($photo->caption))
                            <figcaption>{{ $photo->caption }}</figcaption>
                        @endif
                    </figure>
                </li>
            @endforeach
        </ul>

        @if ($galleryImageCount > 0)
            <p class="photos-cta">
                <a class="btn btn-yellow btn-large" href="{{ route('gallery') }}">Zur Bildergalerie ({{ $galleryImageCount }})</a>
            </p>
        @endif
    </div>
</section>
