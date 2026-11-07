# Uploads / Spatie Media Library

Alle Uploads laufen über die Spatie Media Library – mit ihrer Standard-API, ohne eigene Datei-Helper.

- Collection-Namen nur über `App\Enums\MediaCollectionEnum`, Conversions über `App\Enums\MediaConversionEnum` – keine String-Literale
- Speichern: `$model->addMediaFromRequest('image')->toMediaCollection(MediaCollectionEnum::X->value)` (bei einem Bild pro Datensatz `singleFile()` an der Collection)
- Verkleinern/Vorschauen: `registerMediaConversions()` mit `->nonQueued()` – kein eigener GD-Code
- Öffentliche Bilder (Disk `uploads`): `getFirstMediaUrl($collection, $conversion)`
- Ablage: `<collection>/<uuid>/datei` (`App\Services\MediaLibrary\CollectionPathGenerator`)
- Laravel Cloud: Bucket mit dem Disk-Namen `uploads` anhängen (öffentlich, nicht „Default“)
