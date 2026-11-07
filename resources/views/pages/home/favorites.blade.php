@use('App\Enums\MediaCollectionEnum')
@use('App\Enums\MediaConversionEnum')
@inject('formatPrice', 'App\Actions\Menu\FormatPriceAction')
@if ($favorites->isNotEmpty())
    <section class="section favorites" aria-labelledby="fav-title">
        <div class="container">
            <header class="section-head">
                <p class="eyebrow">Brunos Empfehlungen</p>
                <h2 id="fav-title">Unsere Lieblinge</h2>
            </header>

            <div class="fav-grid">
                @foreach ($favorites as $item)
                    @php($image = $item->getFirstMediaUrl(MediaCollectionEnum::MenuItems->value, MediaConversionEnum::Web->value))
                    <article @class(['fav-card', 'no-image' => $image === ''])>
                        @if ($image !== '')
                            <button class="fav-media js-lightbox" type="button"
                                    data-src="{{ $image }}" data-title="{{ $item->name }}"
                                    aria-label="Bild von {{ $item->name }} vergrößern">
                                <img src="{{ $image }}" alt="{{ $item->name }}" loading="lazy">
                            </button>
                        @else
                            <div class="fav-media fav-placeholder" aria-hidden="true">
                                <span class="fav-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        @endif
                        <div class="fav-body">
                            <h3>{{ $item->name }}</h3>
                            @if (filled($item->description))
                                <p>{{ $item->description }}</p>
                            @endif
                            <p class="fav-price">{{ $formatPrice->execute($item->price_cents) }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
