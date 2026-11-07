/* Klik "Mari Bertanya": riak -> gelombang biru -> pindah ke halaman tanya jawab */
(function () {
    'use strict';
    const cta  = document.getElementById('cta');
    const wipe = document.getElementById('wipe');
    let going = false;

    cta.addEventListener('click', function (e) {
        e.preventDefault();
        if (going) return;
        going = true;

        const r = cta.getBoundingClientRect();
        const d = Math.max(r.width, r.height);
        const rip = document.createElement('span');
        rip.className = 'ripple';
        rip.style.cssText = 'width:' + d + 'px;height:' + d + 'px;left:' + (e.clientX - r.left - d / 2) + 'px;top:' + (e.clientY - r.top - d / 2) + 'px';
        cta.appendChild(rip);

        wipe.style.left = (r.left + r.width / 2 - 10) + 'px';
        wipe.style.top  = (r.top + r.height / 2 - 10) + 'px';
        wipe.classList.add('go');

        setTimeout(function () { window.location.href = cta.getAttribute('href'); }, 700);
    });

    // Tombol Back di browser: reset animasi
    window.addEventListener('pageshow', function (ev) {
        if (ev.persisted) { going = false; wipe.classList.remove('go'); }
    });
})();
