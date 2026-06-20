/* Niyanta core front-end behaviour (vanilla JS). */
(function () {
    'use strict';

    // Apply any locally remembered theme immediately to avoid a flash.
    try {
        var stored = localStorage.getItem('niyanta_theme');
        if (stored && document.documentElement.getAttribute('data-bs-theme') !== stored) {
            document.documentElement.setAttribute('data-bs-theme', stored);
        }
    } catch (e) { /* ignore */ }

    document.addEventListener('DOMContentLoaded', function () {
        // --- Dark / light theme toggle (top-right) ---
        var toggle = document.getElementById('themeToggle');
        if (toggle) {
            toggle.addEventListener('click', function () {
                var current = document.documentElement.getAttribute('data-bs-theme') || 'light';
                var next = current === 'dark' ? 'light' : 'dark';

                document.documentElement.setAttribute('data-bs-theme', next);
                try { localStorage.setItem('niyanta_theme', next); } catch (e) { /* ignore */ }

                var icon = toggle.querySelector('i');
                if (icon) {
                    icon.className = 'bi ' + (next === 'dark' ? 'bi-sun' : 'bi-moon-stars');
                }

                // Persist server-side for the logged-in user.
                var body = new URLSearchParams();
                body.set('theme', next);
                body.set('_csrf', toggle.getAttribute('data-csrf') || '');
                fetch(toggle.getAttribute('data-theme-url'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'fetch'
                    },
                    body: body.toString()
                }).catch(function () { /* preference still applied locally */ });
            });
        }

        // --- Mobile sidebar toggle ---
        var sidebarToggle = document.getElementById('sidebarToggle');
        var sidebar = document.getElementById('appSidebar');
        var backdrop = document.getElementById('sidebarBackdrop');
        function closeSidebar() {
            if (sidebar) { sidebar.classList.remove('open'); }
            if (backdrop) { backdrop.classList.remove('show'); }
        }
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function () {
                var open = sidebar.classList.toggle('open');
                if (backdrop) { backdrop.classList.toggle('show', open); }
            });
        }
        if (backdrop) { backdrop.addEventListener('click', closeSidebar); }
    });
})();
