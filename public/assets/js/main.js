/* Bruno's Imbiss – kleine Helfer für Navigation & Bilder */
(function () {
    'use strict';

    // Mobile Navigation
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Menü schließen' : 'Menü öffnen');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Bilder vergrößern – Bilder mit gleichem data-gallery lassen sich durchblättern
    var box = document.getElementById('lightbox');
    if (!box || typeof box.showModal !== 'function') return;

    var img = box.querySelector('img');
    var title = box.querySelector('.lightbox-title');
    var prev = box.querySelector('.lightbox-prev');
    var next = box.querySelector('.lightbox-next');
    var group = [];
    var index = 0;

    function show(i) {
        index = (i + group.length) % group.length;
        var el = group[index];
        img.src = el.dataset.src;
        img.alt = el.dataset.title || '';
        title.textContent = el.dataset.title || '';
        title.hidden = !el.dataset.title;
        prev.hidden = next.hidden = group.length < 2;
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-lightbox');
        if (!btn) return;
        var name = btn.dataset.gallery;
        group = name ? Array.prototype.slice.call(document.querySelectorAll('.js-lightbox[data-gallery="' + name + '"]')) : [btn];
        show(group.indexOf(btn));
        box.showModal();
    });

    prev.addEventListener('click', function () { show(index - 1); });
    next.addEventListener('click', function () { show(index + 1); });

    box.addEventListener('click', function (e) {
        if (e.target === box) box.close();
    });
    box.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
    });

    // Wischen auf dem Handy
    var startX = null;
    box.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    box.addEventListener('touchend', function (e) {
        if (startX === null || group.length < 2) return;
        var dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 50) show(index + (dx < 0 ? 1 : -1));
        startX = null;
    });
})();
