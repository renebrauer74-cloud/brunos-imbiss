@php($editing = $category->exists)
<x-layouts.admin :title="$editing ? 'Kategorie bearbeiten' : 'Neue Kategorie'" active="menu">
    <div class="page-head">
        <div>
            <a class="back-link" href="{{ route('admin.menu.index') }}">← Zur Speisekarte</a>
            <h1>{{ $editing ? 'Kategorie bearbeiten' : 'Neue Kategorie' }}</h1>
        </div>
    </div>

    <form method="post" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="form card narrow">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif
        <label class="field">
            <span>Name *</span>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="120" placeholder="z. B. Burger">
        </label>
        <label class="field">
            <span>Beschreibung <small>(optional, erscheint unter der Überschrift)</small></span>
            <textarea name="description" rows="2" maxlength="500" placeholder="z. B. Alle Burger mit frischem Salat">{{ old('description', $category->description) }}</textarea>
        </label>
        <input type="hidden" name="visible" value="0">
        <label class="switch">
            <input type="checkbox" name="visible" value="1" @checked(old('visible', $category->visible))>
            <span class="switch-ui" aria-hidden="true"></span>
            <span>Auf der Website anzeigen</span>
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Speichern</button>
            <a class="btn btn-ghost" href="{{ route('admin.menu.index') }}">Abbrechen</a>
        </div>
    </form>

    @if ($editing)
        <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="danger-zone narrow">
            @csrf
            @method('DELETE')
            <p class="muted">
                {{ $itemCount > 0 ? "Achtung: Beim Löschen werden auch die {$itemCount} Artikel dieser Kategorie entfernt." : 'Diese Kategorie enthält keine Artikel.' }}
            </p>
            <button class="btn btn-danger" type="submit"
                    data-confirm="Kategorie „{{ $category->name }}“{{ $itemCount > 0 ? " mit {$itemCount} Artikeln" : '' }} wirklich löschen?">Kategorie löschen</button>
        </form>
    @endif
</x-layouts.admin>
