<x-layouts.site title="Datenschutz" :noindex="true">
    <main id="main" class="section legal">
        <div class="container legal-inner">
            <h1>Datenschutzerklärung</h1>
            @if (filled($settings->privacy))
                <div class="legal-text">{!! nl2br(e($settings->privacy), false) !!}</div>
            @else
                <h2>Verantwortlich</h2>
                <p>Verantwortlich für die Datenverarbeitung auf dieser Website ist der im
                    <a href="{{ route('imprint') }}">Impressum</a> genannte Betreiber.</p>

                <h2>Aufruf der Website</h2>
                <p>Beim Besuch dieser Website verarbeitet unser Hosting-Anbieter technisch notwendige Daten
                    (z.&nbsp;B. IP-Adresse, Datum und Uhrzeit des Abrufs, aufgerufene Seite, Browsertyp) in sogenannten
                    Server-Logfiles. Dies dient der sicheren und stabilen Bereitstellung der Website
                    (Art.&nbsp;6 Abs.&nbsp;1 lit.&nbsp;f DSGVO).</p>

                <h2>Cookies, Tracking & externe Inhalte</h2>
                <p>Diese Website verwendet keine Cookies zu Analyse- oder Werbezwecken und bindet keine
                    Tracking-Dienste, externen Schriftarten oder Karten ein. Es werden lediglich technisch notwendige
                    Cookies (Sitzung und Schutz vor gefälschten Formularanfragen) gesetzt, die beim Schließen des
                    Browsers bzw. nach kurzer Zeit verfallen (§&nbsp;25 Abs.&nbsp;2 TDDDG). Der Link „Route planen“ führt zu
                    Google Maps; erst beim Anklicken werden Daten an Google übertragen.</p>
                <p>Auf der Startseite zeigen wir unsere Auszeichnung von Restaurant Guru. Das Abzeichen (Grafik und
                    Gestaltung) wird vom Server awards.infcdn.net geladen; dabei wird technisch bedingt deine IP-Adresse
                    an den Anbieter übermittelt. Rechtsgrundlage ist unser berechtigtes Interesse, die Auszeichnung
                    darzustellen (Art.&nbsp;6 Abs.&nbsp;1 lit.&nbsp;f DSGVO).</p>

                <h2>Kontakt</h2>
                <p>Wenn du uns per Telefon oder E-Mail kontaktierst, verarbeiten wir deine Angaben nur zur
                    Bearbeitung deiner Anfrage.</p>

                <h2>Deine Rechte</h2>
                <p>Du hast das Recht auf Auskunft, Berichtigung, Löschung und Einschränkung der Verarbeitung deiner
                    Daten, auf Datenübertragbarkeit, auf Widerspruch sowie auf Beschwerde bei einer
                    Datenschutz-Aufsichtsbehörde.</p>
            @endif
            <p><a class="btn btn-outline" href="{{ route('home') }}">← Zur Startseite</a></p>
        </div>
    </main>
</x-layouts.site>
