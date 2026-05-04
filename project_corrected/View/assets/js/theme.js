(function () {
    var html = document.documentElement;
    html.setAttribute('data-theme', localStorage.getItem('theme') || 'light');

    var SUN = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>';
    var MOON = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

    function current() { return html.getAttribute('data-theme') || 'light'; }

    function render() {
        var btn = document.getElementById('theme-toggle');
        if (btn) btn.innerHTML = current() === 'dark' ? SUN : MOON;
    }

    function apply(t) {
        html.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        render();
    }

    function init() {
        var btn = document.getElementById('theme-toggle');
        if (!btn) return;
        render();
        btn.addEventListener('click', function () {
            apply(current() === 'dark' ? 'light' : 'dark');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
