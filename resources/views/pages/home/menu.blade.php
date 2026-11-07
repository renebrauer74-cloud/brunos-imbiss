@use('App\Enums\MediaCollectionEnum')
@use('App\Enums\MediaConversionEnum')
@use('Illuminate\Support\Str')
@inject('formatPrice', 'App\Actions\Menu\FormatPriceAction')
<section class="section menu" id="speisekarte" aria-labelledby="menu-title">
    <div class="container">
        <header class="section-head">
            <p class="eyebrow">Frisch für dich</p>
            <h2 id="menu-title">Speisekarte</h2>
        </header>
    </div>

    @if ($categories->isEmpty())
        <div class="container"><p class="empty-hint">Die Speisekarte wird gerade aktualisiert.</p></div>
    @else
        <div class="container menu-body">
            @foreach ($categories as $category)
                @php($slug = Str::slug(str_replace('&', 'und', $category->name), language: 'de') ?: 'kategorie-'.$category->id)
                <section class="menu-category" id="{{ $slug }}" aria-labelledby="h-{{ $slug }}">
                    <h3 class="menu-category-title" id="h-{{ $slug }}">{{ $category->name }}</h3>
                    @if (filled($category->description))
                        <p class="menu-category-desc">{{ $category->description }}</p>
                    @endif

                    <ul class="menu-list">
                        @foreach ($category->menuItems as $item)
                            @php($thumb = $item->getFirstMediaUrl(MediaCollectionEnum::MenuItems->value, MediaConversionEnum::Thumb->value))
                            <li class="menu-item">
                                @if ($thumb !== '')
                                    <button class="menu-thumb js-lightbox" type="button"
                                            data-src="{{ $item->getFirstMediaUrl(MediaCollectionEnum::MenuItems->value, MediaConversionEnum::Web->value) }}"
                                            data-title="{{ $item->name }}"
                                            aria-label="Bild von {{ $item->name }} vergrößern">
                                        <img src="{{ $thumb }}" alt="" loading="lazy" width="72" height="72">
                                    </button>
                                @endif
                                <div class="menu-item-main">
                                    <div class="menu-item-line">
                                        <span class="menu-item-name">{{ $item->name }}</span>
                                        <span class="leader" aria-hidden="true"></span>
                                        <span class="menu-item-price">{{ $formatPrice->execute($item->price_cents) }}</span>
                                    </div>
                                    @if (filled($item->description) || $item->tags->isNotEmpty() || $item->featured)
                                        <div class="menu-item-meta">
                                            @if ($item->featured)
                                                <span class="tag tag-fav">Empfehlung</span>
                                            @endif
                                            @foreach ($item->tags as $tag)
                                                <span class="tag tag-{{ $tag->value }}">{{ $tag->label() }}</span>
                                            @endforeach
                                            @if (filled($item->description))
                                                <span class="menu-item-desc">{{ $item->description }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach

            <p class="menu-footnote">Alle Preise in Euro inkl. MwSt. Informationen zu Allergenen und Zusatzstoffen erhältst du gerne bei uns am Imbiss.</p>
        </div>
    @endif
</section>
