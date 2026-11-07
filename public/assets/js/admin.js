/* Bruno's Imbiss – Admin-Helfer */
(function () {
    'use strict';

    // Sicherheitsabfrage vor dem Löschen
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-confirm]');
        if (el && !window.confirm(el.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });

    // Preise: geänderte Felder markieren und Speichern-Leiste einblenden
    var priceInputs = document.querySelectorAll('.price-input input');
    var bar = document.getElementById('save-bar');
    var counter = document.getElementById('dirty-count');
    var dirty = false;

    function updateDirty() {
        var n = 0;
        priceInputs.forEach(function (inp) {
            var changed = inp.value.trim() !== inp.dataset.original;
            inp.classList.toggle('is-dirty', changed);
            if (changed) n++;
        });
        dirty = n > 0;
        if (bar) bar.hidden = !dirty;
        if (counter) counter.textContent = n;
    }

    priceInputs.forEach(function (inp) {
        inp.addEventListener('input', updateDirty);
        inp.addEventListener('focus', function () { inp.select(); });
    });

    var reset = document.getElementById('reset-prices');
    if (reset) {
        reset.addEventListener('click', function () {
            priceInputs.forEach(function (inp) { inp.value = inp.dataset.original; });
            updateDirty();
        });
    }

    // Warnen, wenn ungespeicherte Preise verloren gehen würden
    var pricesForm = document.getElementById('prices');
    var submittingPrices = false;
    if (pricesForm) {
        pricesForm.addEventListener('submit', function () { submittingPrices = true; });
    }
    // Schnell-Aktionen (#act, #act-delete) – die Buttons setzen ihr Ziel per formaction
    document.querySelectorAll('#act, #act-delete').forEach(function (actForm) {
        actForm.addEventListener('submit', function (e) {
            if (dirty && !window.confirm('Du hast geänderte Preise noch nicht gespeichert. Trotzdem fortfahren?')) {
                e.preventDefault();
            } else {
                submittingPrices = true; // keine zweite Warnung
            }
        });
    });
    window.addEventListener('beforeunload', function (e) {
        if (dirty && !submittingPrices) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Große Fotos (z. B. vom Handy) schon im Browser verkleinern – spart Upload-Zeit
    // und vermeidet Probleme mit Upload-Limits beim Hoster
    var MAX_SIDE = 2000;
    function downscale(file) {
        if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 1.5 * 1024 * 1024 || !window.createImageBitmap) {
            return Promise.resolve(file);
        }
        return createImageBitmap(file).then(function (bmp) {
            var scale = Math.min(1, MAX_SIDE / Math.max(bmp.width, bmp.height));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(bmp.width * scale);
            canvas.height = Math.round(bmp.height * scale);
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(bmp, 0, 0, canvas.width, canvas.height);
            return new Promise(function (resolve) {
                canvas.toBlob(function (blob) {
                    resolve(blob ? new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file);
                }, 'image/jpeg', 0.88);
            });
        }).catch(function () { return file; });
    }

    var pending = 0;
    document.querySelectorAll('.js-image-input').forEach(function (input) {
        var label = input.closest('.upload');
        var preview = label && label.querySelector('.js-preview');
        var hint = label && label.querySelector('.upload-hint');
        var count = label && label.querySelector('.js-file-count');

        input.addEventListener('change', function () {
            var files = Array.prototype.slice.call(input.files || []);
            if (!files.length) return;

            if (preview) {
                preview.src = URL.createObjectURL(files[0]);
                preview.hidden = false;
                if (hint) hint.hidden = true;
            }
            if (count) count.textContent = files.length === 1 ? '1 Bild ausgewählt' : files.length + ' Bilder ausgewählt';

            // Felder mit data-autosubmit (z. B. Lieblinge) speichern sofort nach der Auswahl
            var autosubmit = function () {
                if (!input.hasAttribute('data-autosubmit')) return;
                if (label) label.classList.add('is-busy');
                input.form.submit();
            };

            if (typeof DataTransfer === 'undefined') { autosubmit(); return; }
            pending++;
            Promise.all(files.map(downscale)).then(function (result) {
                var dt = new DataTransfer();
                result.forEach(function (f) { dt.items.add(f); });
                input.files = dt.files;
            }).finally(function () {
                pending--;
                autosubmit();
            });
        });

        if (label) {
            ['dragenter', 'dragover'].forEach(function (ev) {
                label.addEventListener(ev, function () { label.classList.add('is-drag'); });
            });
            ['dragleave', 'drop'].forEach(function (ev) {
                label.addEventListener(ev, function () { label.classList.remove('is-drag'); });
            });
        }
    });

    // Nicht absenden, solange Bilder noch verkleinert werden
    document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (pending > 0) {
                e.preventDefault();
                var btn = e.submitter;
                var wait = setInterval(function () {
                    if (pending === 0) {
                        clearInterval(wait);
                        form.requestSubmit(btn || undefined);
                    }
                }, 150);
            }
        });
    });

    // Öffnungszeiten: Ruhetag graut Zeiten aus, Montag übertragen
    document.querySelectorAll('.js-closed').forEach(function (cb) {
        cb.addEventListener('change', function () {
            cb.closest('.hours-row').classList.toggle('is-closed', cb.checked);
        });
    });
    var copy = document.getElementById('copy-monday');
    if (copy) {
        copy.addEventListener('click', function () {
            var get = function (day, f) {
                // bei "closed" die Checkbox, nicht das versteckte Feld davor
                return document.querySelector((f === 'closed' ? 'input[type="checkbox"]' : 'input') + '[name="hours[' + day + '][' + f + ']"]');
            };
            ['di', 'mi', 'do', 'fr'].forEach(function (d) {
                get(d, 'from').value = get('mo', 'from').value;
                get(d, 'to').value = get('mo', 'to').value;
                get(d, 'closed').checked = get('mo', 'closed').checked;
                get(d, 'closed').dispatchEvent(new Event('change'));
            });
        });
    }
})();
