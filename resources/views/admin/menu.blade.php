@use('App\Enums\MediaCollectionEnum')
@use('App\Enums\MediaConversionEnum')
@inject('formatPrice', 'App\Actions\Menu\FormatPriceAction')
<x-layouts.admin title="Speisekarte" active="menu">
    <div class="page-head">
        <div>
            <h1>Speisekarte</h1>
            <p class="muted">{{ $total }} Artikel · {{ $hidden }} ausgeblendet · {{ $withImage }} mit Bild</p>
        </div>
        <div class="page-actions">
            <a class="btn" href="{{ route('admin.categories.create') }}">+ Kategorie</a>
            <a class="btn btn-primary" href="{{ route('admin.items.create') }}">+ Neuer Artikel</a>
        </div>
    </div>

    @if ($todo !== [])
        <div class="alert alert-warning">
            <strong>Noch offen:</strong> {{ implode(', ', $todo) }} –
            <a href="{{ route('admin.settings.edit') }}">jetzt in den Einstellungen eintragen</a>.
        </div>
    @endif

    {{-- Gemeinsame Formulare für die Schnell-Aktionen – die Buttons setzen per formaction das Ziel --}}
    <form id="act" method="post">@csrf</form>
    <form id="act-delete" method="post">@csrf @method('DELETE')</form>
    <form id="prices" method="post" action="{{ route('admin.menu.prices') }}">@csrf @method('PUT')</form>

    <nav class="cat-jump" aria-label="Zu Kategorie springen">
        @foreach ($categories as $category)
            <a href="#cat-{{ $category->id }}">{{ $category->name }} <small>{{ $category->menuItems->count() }}</small></a>
        @endforeach
    </nav>

    @if ($categories->isEmpty())
        <div class="card empty">Noch keine Kategorien. <a href="{{ route('admin.categories.create') }}">Erste Kategorie anlegen</a></div>
    @endif

    @foreach ($categories as $category)
        <section @class(['card', 'cat-card', 'is-off' => ! $category->visible]) id="cat-{{ $category->id }}">
            <header class="cat-head">
                <div class="cat-title">
                    <h2>{{ $category->name }}</h2>
                    @unless ($category->visible)
                        <span class="badge badge-off">Ausgeblendet</span>
                    @endunless
                </div>
                <div class="cat-actions">
                    <button class="icon-btn" form="act" formaction="{{ route('admin.categories.move', $category) }}" name="direction" value="-1"
                            title="Kategorie nach oben" @disabled($loop->first)>↑</button>
                    <button class="icon-btn" form="act" formaction="{{ route('admin.categories.move', $category) }}" name="direction" value="1"
                            title="Kategorie nach unten" @disabled($loop->last)>↓</button>
                    <a class="btn btn-small" href="{{ route('admin.categories.edit', $category) }}">Bearbeiten</a>
                    <a class="btn btn-small btn-primary" href="{{ route('admin.items.create', ['category' => $category->id]) }}">+ Artikel</a>
                </div>
            </header>

            @if ($category->menuItems->isEmpty())
                <p class="muted empty-cat">Noch keine Artikel in dieser Kategorie.</p>
            @endif

            <ul class="item-list">
                @foreach ($category->menuItems as $item)
                    @php($thumb = $item->getFirstMediaUrl(MediaCollectionEnum::MenuItems->value, MediaConversionEnum::Thumb->value))
                    @php($price = $formatPrice->execute($item->price_cents, withCurrency: false))
                    <li @class(['item-row', 'is-off' => ! $item->visible])>
                        <a class="item-thumb" href="{{ route('admin.items.edit', $item) }}" aria-label="{{ $item->name }} bearbeiten">
                            @if ($thumb !== '')
                                <img src="{{ $thumb }}" alt="" loading="lazy">
                            @else
                                <span aria-hidden="true">📷</span>
                            @endif
                        </a>

                        <div class="item-info">
                            <a class="item-name" href="{{ route('admin.items.edit', $item) }}">{{ $item->name }}</a>
                            <div class="item-badges">
                                @if ($item->featured)
                                    <span class="badge badge-fav">★ Empfehlung</span>
                                @endif
                                @unless ($item->visible)
                                    <span class="badge badge-off">Ausgeblendet</span>
                                @endunless
                                @foreach ($item->tags as $tag)
                                    <span class="badge">{{ $tag->label() }}</span>
                                @endforeach
                            </div>
                        </div>

                        <label class="price-input">
                            <span class="visually-hidden">Preis für {{ $item->name }}</span>
                            <input form="prices" type="text" inputmode="decimal" name="price[{{ $item->id }}]"
                                   value="{{ $price }}" data-original="{{ $price }}" autocomplete="off">
                            <span class="suffix">€</span>
                        </label>

                        <div class="item-actions">
                            <button class="icon-btn" form="act" formaction="{{ route('admin.items.toggle', $item) }}"
                                    title="{{ $item->visible ? 'Auf der Website ausblenden' : 'Auf der Website anzeigen' }}">{{ $item->visible ? '👁' : '🚫' }}</button>
                            <button @class(['icon-btn', 'is-on' => $item->featured]) form="act" formaction="{{ route('admin.items.feature', $item) }}"
                                    title="{{ $item->featured ? 'Nicht mehr empfehlen' : 'Als Empfehlung auf der Startseite zeigen' }}">★</button>
                            <button class="icon-btn" form="act" formaction="{{ route('admin.items.move', $item) }}" name="direction" value="-1"
                                    title="Nach oben" @disabled($loop->first)>↑</button>
                            <button class="icon-btn" form="act" formaction="{{ route('admin.items.move', $item) }}" name="direction" value="1"
                                    title="Nach unten" @disabled($loop->last)>↓</button>
                            <button class="icon-btn icon-danger" form="act-delete" formaction="{{ route('admin.items.destroy', $item) }}"
                                    title="Löschen" data-confirm="„{{ $item->name }}“ wirklich löschen?">🗑</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach

    <div class="save-bar" id="save-bar" hidden>
        <span><strong id="dirty-count">0</strong> Preis(e) geändert</span>
        <div>
            <button class="btn" type="button" id="reset-prices">Verwerfen</button>
            <button class="btn btn-primary" type="submit" form="prices">Preise speichern</button>
        </div>
    </div>
</x-layouts.admin>
