@use('App\Enums\MediaCollectionEnum')
@use('App\Enums\MediaConversionEnum')
@inject('formatPrice', 'App\Actions\Menu\FormatPriceAction')
<x-layouts.admin title="Lieblinge" active="favorites">
    <div class="page-head">
        <div>
            <h1>Unsere Lieblinge</h1>
            <p class="muted">Diese Gerichte stehen groß auf der Startseite. Ideal sind <strong>4</strong> (oder 2, 6, 8).</p>
        </div>
        <div class="page-actions">
            <a class="btn" href="{{ route('home') }}" target="_blank" rel="noopener">Startseite ansehen ↗</a>
        </div>
    </div>

    <form id="act" method="post">@csrf</form>
    <form id="act-delete" method="post">@csrf @method('DELETE')</form>

    @if ($favorites->isEmpty())
        <div class="card empty">Noch keine Lieblinge – füge unten ein Gericht hinzu. Ohne Lieblinge wird der Bereich auf der Startseite ausgeblendet.</div>
    @else
        <ul class="fav-admin">
            @foreach ($favorites as $favorite)
                @php
                    $hidden = ! $favorite->visible || ! $favorite->category->visible;
                    $image = $favorite->getFirstMediaUrl(MediaCollectionEnum::MenuItems->value, MediaConversionEnum::Thumb->value);
                @endphp
                <li @class(['fav-admin-card', 'is-off' => $hidden])>
                    <span class="fav-admin-pos">{{ $loop->iteration }}</span>

                    <form method="post" action="{{ route('admin.favorites.photo.store', $favorite) }}" enctype="multipart/form-data" class="fav-admin-photo">
                        @csrf
                        <label class="upload">
                            @if ($image !== '')
                                <img class="js-preview" src="{{ $image }}" alt="">
                            @else
                                <img class="js-preview" src="" alt="" hidden>
                                <span class="upload-hint">
                                    <span class="fav-admin-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <strong>📷 Foto einfügen</strong>
                                </span>
                            @endif
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="js-image-input" data-autosubmit
                                   aria-label="Foto für {{ $favorite->name }} wählen">
                        </label>
                    </form>

                    <div class="fav-admin-body">
                        <strong>{{ $favorite->name }}</strong>
                        <span class="muted">{{ $favorite->category->name }} · {{ $formatPrice->execute($favorite->price_cents) }}</span>
                        @if ($hidden)
                            <span class="badge badge-off">Ausgeblendet – erscheint nicht</span>
                        @endif
                    </div>

                    <div class="fav-admin-actions">
                        <button class="icon-btn" form="act" formaction="{{ route('admin.favorites.move', $favorite) }}" name="direction" value="-1"
                                title="Nach vorne" @disabled($loop->first)>←</button>
                        <button class="icon-btn" form="act" formaction="{{ route('admin.favorites.move', $favorite) }}" name="direction" value="1"
                                title="Nach hinten" @disabled($loop->last)>→</button>
                        @if ($image !== '')
                            <button class="btn btn-small" form="act-delete" formaction="{{ route('admin.favorites.photo.destroy', $favorite) }}"
                                    data-confirm="Foto von „{{ $favorite->name }}“ löschen?">Foto löschen</button>
                        @endif
                        <button class="btn btn-small btn-danger" form="act-delete" formaction="{{ route('admin.favorites.destroy', $favorite) }}"
                                title="Gericht bleibt in der Speisekarte">Entfernen</button>
                    </div>
                </li>
            @endforeach
        </ul>
        <p class="muted fav-admin-note">Tipp: Foto antippen, um es auszutauschen. Das Foto erscheint auch als kleines Bild in der Speisekarte.</p>
    @endif

    <form method="post" action="{{ route('admin.favorites.store') }}" class="card form fav-add">
        @csrf
        <h2>Gericht hinzufügen</h2>
        <div class="fav-add-row">
            <label class="field">
                <span class="visually-hidden">Gericht</span>
                <select name="item_id" required>
                    <option value="">– Gericht wählen –</option>
                    @foreach ($categories as $category)
                        <optgroup label="{{ $category->name }}">
                            @foreach ($category->menuItems as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $formatPrice->execute($item->price_cents) }}){{ $item->visible ? '' : ' – ausgeblendet' }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-primary" type="submit">+ Hinzufügen</button>
        </div>
    </form>
</x-layouts.admin>
