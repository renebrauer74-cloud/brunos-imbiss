@use('App\Enums\MediaCollectionEnum')
@use('App\Enums\MediaConversionEnum')
@use('App\Enums\MenuItemTagEnum')
@php
    $editing = $item->exists;
    $title = $editing ? 'Artikel bearbeiten' : 'Neuer Artikel';
    $image = $editing ? $item->getFirstMediaUrl(MediaCollectionEnum::MenuItems->value, MediaConversionEnum::Web->value) : '';
    $selectedTags = old('tags', $item->tags?->map->value->all() ?? []);
@endphp
<x-layouts.admin :title="$title" active="menu">
    <div class="page-head">
        <div>
            <a class="back-link" href="{{ route('admin.menu.index') }}#cat-{{ $item->category_id }}">← Zur Speisekarte</a>
            <h1>{{ $title }}</h1>
        </div>
    </div>

    <form method="post" action="{{ $editing ? route('admin.items.update', $item) : route('admin.items.store') }}"
          enctype="multipart/form-data" class="form card form-grid">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="form-main">
            <label class="field">
                <span>Name *</span>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required maxlength="160" placeholder="z. B. Currywurst Pommes">
            </label>

            <div class="field-row">
                <label class="field">
                    <span>Preis *</span>
                    <span class="input-suffix">
                        <input type="text" name="price" value="{{ old('price', $price) }}" required inputmode="decimal" placeholder="0,00" autocomplete="off">
                        <span>€</span>
                    </span>
                </label>
                <label class="field">
                    <span>Kategorie</span>
                    <select name="category_id">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $item->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label class="field">
                <span>Beschreibung <small>(optional)</small></span>
                <textarea name="description" rows="3" maxlength="500" placeholder="z. B. mit hausgemachter Currysoße und Röstzwiebeln">{{ old('description', $item->description) }}</textarea>
            </label>

            <fieldset class="field">
                <legend>Kennzeichnung</legend>
                <div class="check-group">
                    @foreach (MenuItemTagEnum::cases() as $tag)
                        <label class="check-pill">
                            <input type="checkbox" name="tags[]" value="{{ $tag->value }}" @checked(in_array($tag->value, $selectedTags, true))>
                            <span>{{ $tag->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="switches">
                <input type="hidden" name="visible" value="0">
                <label class="switch">
                    <input type="checkbox" name="visible" value="1" @checked(old('visible', $item->visible))>
                    <span class="switch-ui" aria-hidden="true"></span>
                    <span>Auf der Website anzeigen</span>
                </label>
                <input type="hidden" name="featured" value="0">
                <label class="switch">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $item->featured))>
                    <span class="switch-ui" aria-hidden="true"></span>
                    <span>Als Empfehlung auf der Startseite zeigen</span>
                </label>
            </div>
        </div>

        <div class="form-side">
            <span class="field-label">Bild</span>
            <label class="upload">
                <img class="js-preview" src="{{ $image }}" alt="" @if ($image === '') hidden @endif>
                <span class="upload-hint" @if ($image !== '') hidden @endif>
                    <strong>📷 Foto auswählen</strong>
                    <small>JPG, PNG oder WebP · max. {{ (int) (config('bruno.uploads.max_kilobytes') / 1024) }} MB<br>Wird automatisch verkleinert.</small>
                </span>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="js-image-input">
            </label>
            @if ($image !== '')
                <label class="check-inline">
                    <input type="checkbox" name="remove_image" value="1"> Bild entfernen
                </label>
            @endif
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Speichern</button>
            @unless ($editing)
                <button class="btn" type="submit" name="save_new" value="1">Speichern & weiteren anlegen</button>
            @endunless
            <a class="btn btn-ghost" href="{{ route('admin.menu.index') }}">Abbrechen</a>
        </div>
    </form>

    @if ($editing)
        <form method="post" action="{{ route('admin.items.destroy', $item) }}" class="danger-zone">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" type="submit" data-confirm="„{{ $item->name }}“ wirklich löschen?">Artikel löschen</button>
        </form>
    @endif
</x-layouts.admin>
