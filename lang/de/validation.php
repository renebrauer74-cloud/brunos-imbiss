<?php

/*
|--------------------------------------------------------------------------
| Validierungsmeldungen (Deutsch)
|--------------------------------------------------------------------------
|
| Nur die Regeln, die diese Anwendung verwendet. Alles andere fällt auf die
| englischen Standardtexte des Frameworks zurück (APP_FALLBACK_LOCALE).
|
*/

return [

    'array' => ':attribute ist ungültig.',
    'boolean' => ':attribute ist ungültig.',
    'confirmed' => 'Die beiden Passwörter stimmen nicht überein.',
    'current_password' => 'Das aktuelle Passwort ist falsch.',
    'date_format' => ':attribute muss im Format :format angegeben werden.',
    'email' => 'Bitte eine gültige E-Mail-Adresse angeben.',
    'enum' => ':attribute ist ungültig.',
    'exists' => 'Bitte eine gültige Auswahl für :attribute treffen.',
    'image' => ':attribute muss ein Bild sein.',
    'integer' => ':attribute muss eine Zahl sein.',
    'max' => [
        'array' => ':attribute darf höchstens :max Einträge haben.',
        'file' => ':attribute ist zu groß (höchstens :max KB).',
        'numeric' => ':attribute darf höchstens :max sein.',
        'string' => ':attribute darf höchstens :max Zeichen lang sein.',
    ],
    'mimes' => 'Bitte nur Bilder hochladen (JPG, PNG, WebP).',
    'min' => [
        'string' => ':attribute muss mindestens :min Zeichen lang sein.',
    ],
    'regex' => ':attribute hat ein ungültiges Format.',
    'required' => 'Bitte :attribute ausfüllen.',
    'string' => ':attribute muss ein Text sein.',
    'uploaded' => ':attribute konnte nicht hochgeladen werden – vermutlich ist die Datei zu groß.',

    'attributes' => [
        'about' => '„Über uns“-Text',
        'caption' => 'Bildunterschrift',
        'captions.*' => 'Bildunterschrift',
        'category_id' => 'Kategorie',
        'current_password' => 'Aktuelles Passwort',
        'description' => 'Beschreibung',
        'email' => 'E-Mail-Adresse',
        'hours_note' => 'Hinweis zu den Öffnungszeiten',
        'image' => 'Das Bild',
        'images' => 'Bilder',
        'images.*' => 'Das Bild',
        'imprint' => 'Impressum',
        'item_id' => 'Gericht',
        'name' => 'Name',
        'notice' => 'Hinweis-Leiste',
        'password' => 'Passwort',
        'phone' => 'Telefonnummer',
        'price' => 'Preis',
        'privacy' => 'Datenschutzerklärung',
        'site_title' => 'Name des Imbiss',
        'slogan' => 'Slogan',
        'street' => 'Straße',
        'tagline' => 'Untertitel',
        'tags.*' => 'Kennzeichnung',
        'username' => 'Benutzername',
        'zip_city' => 'PLZ & Ort',
    ],

];
