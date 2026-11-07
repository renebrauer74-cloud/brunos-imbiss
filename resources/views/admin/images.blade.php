@use('App\Enums\MediaCollectionEnum')
@use('App\Enums\MediaConversionEnum')
<x-layouts.admin title="Bilder" active="images">
    <div class="page-head">
        <div>
            <h1>Bilder</h1>
            <p class="muted">Fotos unter der Speisekarte und die Bildergalerie.</p>
        </div>
        <div class="page-actions">
            <a class="btn" href="{{ route('home') }}#fotos" target="_blank" rel="noopener">Startseite ansehen ↗</a>
            <a class="btn" href="{{ route('gallery') }}" target="_blank" rel="noopener">Galerie ansehen ↗</a>
        </div>
    </div>

    {{-- Startseite --}}
    <form method="post" action="{{ route('admin.home-photos.update') }}" enctype="multipart/form-data" class="card form" id="startseite">
        @csrf
        @method('PUT')
        <h2>Unter der Speisekarte <small>(3 Fotos, leicht gedreht angeordnet)</small></h2>
        <p class="muted">Ohne Foto wird ein bunter Platzhalter angezeigt.</p>

        <div class="home-slots">
            @foreach ($homePhotos as $photo)
                @php
                    $slot = $photo->slot->value;
                    $image = $photo->getFirstMediaUrl(MediaCollectionEnum::HomePhotos->value, MediaConversionEnum::Thumb->value);
                @endphp
                <div class="home-slot">
                    <span class="field-label">Foto {{ $slot }}</span>
                    <label class="upload upload-square">
                        <img class="js-preview" src="{{ $image }}" alt="" @if ($image === '') hidden @endif>
                        <span class="upload-hint" @if ($image !== '') hidden @endif>
                            <strong>{{ $photo->slot->emoji() }} Foto wählen</strong>
                            <small>Am besten quadratisch</small>
                        </span>
                        <input type="file" name="photos[{{ $slot }}][image]" accept="image/jpeg,image/png,image/webp" class="js-image-input">
                    </label>
                    <label class="field">
                        <span>Bildunterschrift</span>
                        <input type="text" name="photos[{{ $slot }}][caption]" value="{{ old("photos.$slot.caption", $photo->caption) }}" maxlength="80">
                    </label>
                    @if ($image !== '')
                        <label class="check-inline"><input type="checkbox" name="photos[{{ $slot }}][remove]" value="1"> Foto entfernen</label>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Startseiten-Bilder speichern</button>
        </div>
    </form>

    {{-- Galerie --}}
    <section class="card" id="galerie">
        <h2>Bildergalerie <small>({{ $gallery->count() }} Bilder)</small></h2>

        <form method="post" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="gallery-upload">
            @csrf
            <label class="upload upload-wide">
                <span class="upload-hint">
                    <strong>📷 Bilder auswählen</strong>
                    <small>Mehrere auf einmal möglich · JPG, PNG oder WebP<br>Werden automatisch verkleinert.</small>
                    <small class="js-file-count"></small>
                </span>
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="js-image-input" required>
            </label>
            <button class="btn btn-primary" type="submit">Hochladen</button>
        </form>

        @if ($gallery->isEmpty())
            <p class="muted">Noch keine Bilder in der Galerie. Sobald du welche hochlädst, erscheint „Galerie“ im Menü der Website.</p>
        @else
            <form id="act" method="post">@csrf</form>
            <form id="act-delete" method="post">@csrf @method('DELETE')</form>
            <form id="captions" method="post" action="{{ route('admin.gallery.captions') }}">@csrf @method('PUT')</form>

            <ul class="gallery-admin">
                @foreach ($gallery as $image)
                    <li @class(['gallery-card', 'is-off' => ! $image->visible])>
                        <div class="gallery-thumb">
                            <img src="{{ $image->getFirstMediaUrl(MediaCollectionEnum::Gallery->value, MediaConversionEnum::Thumb->value) }}" alt="" loading="lazy">
                            @unless ($image->visible)
                                <span class="badge badge-off">Ausgeblendet</span>
                            @endunless
                        </div>
                        <input form="captions" type="text" name="captions[{{ $image->id }}]" value="{{ $image->caption }}"
                               placeholder="Bildunterschrift (optional)" maxlength="160" aria-label="Bildunterschrift">
                        <div class="item-actions">
                            <button class="icon-btn" form="act" formaction="{{ route('admin.gallery.toggle', $image) }}"
                                    title="{{ $image->visible ? 'Ausblenden' : 'Anzeigen' }}">{{ $image->visible ? '👁' : '🚫' }}</button>
                            <button class="icon-btn" form="act" formaction="{{ route('admin.gallery.move', $image) }}" name="direction" value="-1"
                                    title="Nach vorne" @disabled($loop->first)>←</button>
                            <button class="icon-btn" form="act" formaction="{{ route('admin.gallery.move', $image) }}" name="direction" value="1"
                                    title="Nach hinten" @disabled($loop->last)>→</button>
                            <button class="icon-btn icon-danger" form="act-delete" formaction="{{ route('admin.gallery.destroy', $image) }}"
                                    title="Löschen" data-confirm="Dieses Bild wirklich löschen?">🗑</button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit" form="captions">Bildunterschriften speichern</button>
            </div>
        @endif
    </section>
</x-layouts.admin>
