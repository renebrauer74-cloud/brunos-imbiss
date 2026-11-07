@use('App\Enums\WeekdayEnum')
@php
    $v = fn (string $field): ?string => old($field, $settings->{$field});
@endphp
<x-layouts.admin title="Einstellungen" active="settings">
    <div class="page-head">
        <div>
            <h1>Einstellungen</h1>
            <p class="muted">Kontaktdaten, Öffnungszeiten und Texte der Website.</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.settings.update') }}" class="form settings-form">
        @csrf
        @method('PUT')

        <section class="card">
            <h2>Aktueller Hinweis</h2>
            <label class="field">
                <span>Hinweis-Leiste ganz oben <small>(leer lassen = ausgeblendet)</small></span>
                <input type="text" name="notice" value="{{ $v('notice') }}" maxlength="240" placeholder="z. B. Am 24.12. haben wir geschlossen · Neu: Wildschwein-Bratwurst!">
            </label>
        </section>

        <section class="card">
            <h2>Standort & Kontakt</h2>
            <div class="field-row">
                <label class="field">
                    <span>Straße & Hausnummer</span>
                    <input type="text" name="street" value="{{ $v('street') }}" maxlength="120" autocomplete="street-address">
                </label>
                <label class="field">
                    <span>PLZ & Ort</span>
                    <input type="text" name="zip_city" value="{{ $v('zip_city') }}" maxlength="120" placeholder="44787 Bochum">
                </label>
            </div>
            <div class="field-row">
                <label class="field">
                    <span>Telefon</span>
                    <input type="tel" name="phone" value="{{ $v('phone') }}" maxlength="40" placeholder="0234 123456">
                </label>
                <label class="field">
                    <span>E-Mail</span>
                    <input type="email" name="email" value="{{ $v('email') }}" maxlength="120">
                </label>
            </div>
        </section>

        <section class="card">
            <h2>Öffnungszeiten</h2>
            <div class="hours-editor">
                @foreach (WeekdayEnum::cases() as $day)
                    @php
                        $key = $day->value;
                        $closed = (bool) old("hours.$key.closed", $hours[$key]['closed']);
                    @endphp
                    <div @class(['hours-row', 'is-closed' => $closed])>
                        <strong>{{ $day->label() }}</strong>
                        <input type="time" name="hours[{{ $key }}][from]" value="{{ old("hours.$key.from", $hours[$key]['from']) }}" aria-label="{{ $day->label() }} von">
                        <span class="muted">bis</span>
                        <input type="time" name="hours[{{ $key }}][to]" value="{{ old("hours.$key.to", $hours[$key]['to']) }}" aria-label="{{ $day->label() }} bis">
                        <input type="hidden" name="hours[{{ $key }}][closed]" value="0">
                        <label class="check-inline">
                            <input type="checkbox" name="hours[{{ $key }}][closed]" value="1" @checked($closed) class="js-closed"> Ruhetag
                        </label>
                    </div>
                @endforeach
            </div>
            <button class="btn btn-small" type="button" id="copy-monday">Montag auf Di – Fr übertragen</button>
            <label class="field">
                <span>Hinweis zu den Öffnungszeiten <small>(optional)</small></span>
                <input type="text" name="hours_note" value="{{ $v('hours_note') }}" maxlength="300" placeholder="z. B. An Feiertagen geschlossen">
            </label>
        </section>

        <section class="card">
            <h2>Texte</h2>
            <div class="field-row">
                <label class="field">
                    <span>Name des Imbiss</span>
                    <input type="text" name="site_title" value="{{ $v('site_title') }}" maxlength="120" required>
                </label>
                <label class="field">
                    <span>Slogan</span>
                    <input type="text" name="slogan" value="{{ $v('slogan') }}" maxlength="160">
                </label>
            </div>
            <label class="field">
                <span>Untertitel unter dem Logo</span>
                <input type="text" name="tagline" value="{{ $v('tagline') }}" maxlength="160">
            </label>
            <label class="field">
                <span>„Über uns“-Text</span>
                <textarea name="about" rows="4" maxlength="2000">{{ $v('about') }}</textarea>
            </label>
        </section>

        <section class="card">
            <h2>Rechtliches</h2>
            <label class="field">
                <span>Impressum <small>(Pflichtangaben: Name des Inhabers, Anschrift, Kontakt, ggf. USt-IdNr.)</small></span>
                <textarea name="imprint" rows="8" maxlength="6000" placeholder="Bruno’s Imbiss&#10;Inhaber: Vorname Nachname&#10;Straße Nr.&#10;PLZ Bochum&#10;&#10;Telefon: …&#10;E-Mail: …&#10;USt-IdNr.: …">{{ $v('imprint') }}</textarea>
            </label>
            <label class="field">
                <span>Datenschutzerklärung <small>(leer lassen = Standardtext wird angezeigt)</small></span>
                <textarea name="privacy" rows="8" maxlength="20000">{{ $v('privacy') }}</textarea>
            </label>
        </section>

        <div class="save-bar is-static">
            <span class="muted">Änderungen sind sofort auf der Website sichtbar.</span>
            <button class="btn btn-primary" type="submit">Einstellungen speichern</button>
        </div>
    </form>
</x-layouts.admin>
