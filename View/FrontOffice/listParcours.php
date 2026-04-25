<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

$controller = new ParcoursController();
$parcours   = $controller->afficherParcours();
$stats      = $controller->statsParcours();

$currentPage = 'catalogue';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Parcours — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:radial-gradient(circle at top left,rgba(15,118,110,.1),transparent 30%),linear-gradient(180deg,#f0fff8,#f7f1e5); }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }

        /* HERO */
        .hero-strip { display:grid; grid-template-columns:1.3fr .7fr; gap:18px; margin-bottom:26px; }
        .hero-card { background:linear-gradient(135deg,#0f766e,#102a43); color:white; border-radius:28px; padding:32px; box-shadow:0 20px 40px rgba(15,118,110,.2); position:relative; overflow:hidden; }
        .hero-card::after { content:'🗺️'; position:absolute; right:24px; bottom:10px; font-size:6rem; opacity:.12; }
        .hero-card h1 { font-size:clamp(1.6rem,3.5vw,2.6rem); line-height:1.1; margin-bottom:10px; }
        .hero-card p { opacity:.9; line-height:1.7; }

        /* STATS */
        .hero-stats { background:white; border-radius:28px; padding:20px 16px; box-shadow:0 12px 28px rgba(16,42,67,.07); display:grid; grid-template-columns:1fr 1fr; grid-template-rows:1fr 1fr; gap:12px; align-content:center; }
        .mini-stat { text-align:center; background:#f8fafc; border-radius:14px; padding:12px 8px; border:1px solid rgba(16,42,67,.06); }
        .mini-stat .val { font-size:1.75rem; font-weight:900; line-height:1; margin-bottom:5px; }
        .mini-stat .lbl { color:#627d98; font-size:0.76rem; font-weight:600; text-transform:uppercase; letter-spacing:.03em; }
        .mini-stat.total .val { color:var(--teal); }
        .mini-stat.facile .val { color:#059669; }
        .mini-stat.moyen .val  { color:#d97706; }
        .mini-stat.hard .val   { color:var(--coral); }

        /* FILTER */
        .filter-section { background:white; border-radius:18px; padding:18px 20px; margin-bottom:24px; box-shadow:0 6px 18px rgba(16,42,67,.06); }
        .filter-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .filter-bar select {
            border-radius:12px; border:1px solid #cbd5e1; padding:10px 14px;
            font:inherit; flex:1 1 140px; min-width:0; max-width:210px;
        }
        .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color:var(--teal); }

        .section-title { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .section-title h2 { font-size:1.8rem; }
        .count-badge { background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:5px 14px; font-weight:700; font-size:.86rem; }

        .catalog { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:22px; }
        .card { background:rgba(255,255,255,.92); border:1px solid rgba(16,42,67,.08); border-radius:24px; box-shadow:0 14px 36px rgba(16,42,67,.08); overflow:hidden; transition:transform .22s,box-shadow .22s; }
        .card:hover { transform:translateY(-5px); box-shadow:0 24px 50px rgba(16,42,67,.13); }
        .diff-banner { padding:12px 20px; font-weight:900; font-size:0.88rem; letter-spacing:.04em; }
        .diff-facile { background:linear-gradient(135deg,#d1fae5,#a7f3d0); color:#065f46; }
        .diff-moyen { background:linear-gradient(135deg,#fef9c3,#fde68a); color:#92400e; }
        .diff-difficile { background:linear-gradient(135deg,#fee2e2,#fecaca); color:#991b1b; }
        .card-body { padding:20px; }
        .marathon-tag { display:inline-block; background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:5px 12px; font-size:0.82rem; font-weight:700; margin-bottom:12px; }
        .card h3 { font-size:1.18rem; margin-bottom:14px; }
        .route-info { background:#f8fafc; border-radius:14px; padding:14px; display:grid; gap:8px; font-size:0.92rem; margin-bottom:14px; }
        .route-row { display:flex; align-items:center; gap:8px; color:#486581; }
        .dist-row { display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid #e6edf3; }
        .dist-val { font-size:1.5rem; font-weight:900; color:var(--teal); }

        .empty-state { text-align:center; padding:60px; color:#627d98; }
        @media(max-width:768px){ .hero-strip{grid-template-columns:1fr;} }

        /* AUTOCOMPLETE */
        .search-wrap { position:relative; flex:2 1 200px; min-width:0; }
        .search-wrap input { width:100%; border-radius:12px; border:1px solid #cbd5e1; padding:10px 14px; font:inherit; }
        .search-wrap input:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        .autocomplete-list { position:absolute; top:calc(100% + 4px); left:0; right:0; background:white; border:1px solid #cbd5e1; border-radius:12px; box-shadow:0 8px 20px rgba(16,42,67,.12); z-index:999; max-height:220px; overflow-y:auto; display:none; }
        .autocomplete-list.open { display:block; }
        .autocomplete-item { padding:10px 14px; cursor:pointer; font-size:0.93rem; border-bottom:1px solid #f1f5f9; }
        .autocomplete-item:last-child { border-bottom:none; }
        .autocomplete-item:hover, .autocomplete-item.selected { background:#f0fdf9; color:var(--teal); font-weight:700; }

        /* Spinner */
        .spinner { display:none; width:18px; height:18px; border:2px solid rgba(15,118,110,.2); border-top-color:var(--teal); border-radius:50%; animation:spin .6s linear infinite; position:absolute; right:12px; top:50%; transform:translateY(-50%); }
        .spinner.active { display:block; }
        @keyframes spin { to { transform:translateY(-50%) rotate(360deg); } }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">

    <section class="hero-strip">
        <div class="hero-card">
            <h1>Parcours de Course</h1>
            <p>Découvrez tous les tracés disponibles par niveau de difficulté.</p>
        </div>
        <div class="hero-stats">
            <div class="mini-stat total">
                <div class="val"><?php echo (int)($stats['total']??0); ?></div>
                <div class="lbl">Total parcours</div>
            </div>
            <div class="mini-stat facile">
                <div class="val"><?php echo (int)($stats['facile']??0); ?></div>
                <div class="lbl">🟢 Faciles</div>
            </div>
            <div class="mini-stat moyen">
                <div class="val"><?php echo (int)($stats['moyen']??0); ?></div>
                <div class="lbl">🟡 Moyens</div>
            </div>
            <div class="mini-stat hard">
                <div class="val"><?php echo (int)($stats['difficile']??0); ?></div>
                <div class="lbl">🔴 Difficiles</div>
            </div>
        </div>
    </section>

    <div class="filter-section">
        <div class="filter-bar" id="filterBar">
            <div class="search-wrap">
                <input type="text" id="searchInput" placeholder="🔍 Rechercher par nom de parcours..." autocomplete="off">
                <div class="spinner" id="searchSpinner"></div>
                <div class="autocomplete-list" id="autocompleteList"></div>
            </div>
            <select id="diffSelect" style="border-radius:12px;border:1px solid #cbd5e1;padding:10px 14px;font:inherit;flex:1 1 140px;min-width:0;max-width:210px;">
                <option value="">Toutes les difficultés</option>
                <option value="facile">🟢 Facile</option>
                <option value="moyen">🟡 Moyen</option>
                <option value="difficile">🔴 Difficile</option>
            </select>
        </div>
    </div>

    <div class="section-title">
        <h2>Tous les Parcours</h2>
        <span class="count-badge" id="countBadge"><?php echo count($parcours); ?> résultats</span>
    </div>

    <div id="catalogWrapper">
    <?php if (empty($parcours)): ?>
        <div class="empty-state">
            <div style="font-size:3rem;margin-bottom:14px;">🗺️</div>
            <h3>Aucun parcours trouvé</h3>
        </div>
    <?php else: ?>
    <section class="catalog">
        <?php foreach ($parcours as $p):
            $dc = ['facile'=>'diff-facile','moyen'=>'diff-moyen','difficile'=>'diff-difficile'][$p['difficulte']] ?? 'diff-moyen';
            $dl = ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$p['difficulte']] ?? $p['difficulte'];
        ?>
        <article class="card">
            <div class="diff-banner <?php echo $dc; ?>"><?php echo $dl; ?></div>
            <div class="card-body">
                <span class="marathon-tag">🏃 <?php echo htmlspecialchars($p['nom_marathon']); ?></span>
                <h3><?php echo htmlspecialchars($p['nom_parcours']); ?></h3>
                <div class="route-info">
                    <div class="route-row">📍 <span><strong>Départ :</strong> <?php echo htmlspecialchars($p['point_depart']); ?></span></div>
                    <div class="route-row">🏁 <span><strong>Arrivée :</strong> <?php echo htmlspecialchars($p['point_arrivee']); ?></span></div>
                </div>
                <div class="dist-row">
                    <div><div class="dist-val"><?php echo number_format((float)$p['distance'],2); ?> km</div><div style="color:#627d98;font-size:.82rem;">Distance totale</div></div>
                    <span style="font-size:2rem;">🏅</span>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
    </div><!-- end catalogWrapper -->

    <div style="height:40px;"></div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script>
(function(){
    const searchInput    = document.getElementById('searchInput');
    const diffSelect     = document.getElementById('diffSelect');
    const autoList       = document.getElementById('autocompleteList');
    const catalogWrapper = document.getElementById('catalogWrapper');
    const countBadge     = document.getElementById('countBadge');
    const spinner        = document.getElementById('searchSpinner');

    let debounceTimer = null;
    let sugTimer = null;
    let selectedIndex = -1;
    let currentSuggestions = [];
    let activeRequest = null;

    const AJAX = 'search_parcours_front.php';

    // ── Fetch cards (AJAX) — preserves focus ─────────────────────────────
    function fetchCards() {
        const search = searchInput.value.trim();
        const diff   = diffSelect.value;

        spinner.classList.add('active');

        // Save cursor position before fetch
        const selStart = searchInput.selectionStart;
        const selEnd   = searchInput.selectionEnd;

        if (activeRequest) activeRequest.abort();
        const ctrl = new AbortController();
        activeRequest = ctrl;

        fetch(AJAX + '?mode=cards&search=' + encodeURIComponent(search) + '&difficulte=' + encodeURIComponent(diff), { signal: ctrl.signal })
            .then(r => r.json())
            .then(data => {
                spinner.classList.remove('active');
                catalogWrapper.innerHTML = data.html;
                countBadge.textContent  = data.count + ' résultat' + (data.count !== 1 ? 's' : '');
                // Restore focus and cursor — key fix for "cursor drops" bug
                searchInput.focus();
                searchInput.setSelectionRange(selStart, selEnd);
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    spinner.classList.remove('active');
                    console.error(err);
                }
            });
    }

    // ── Fetch suggestions (autocomplete) ─────────────────────────────────
    function fetchSuggestions(val) {
        if (val.length === 0) { closeAuto(); return; }
        fetch(AJAX + '?mode=suggestions&search=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(names => {
                currentSuggestions = names;
                selectedIndex = -1;
                if (names.length === 0) { closeAuto(); return; }
                autoList.innerHTML = names.map((n, i) =>
                    '<div class="autocomplete-item" data-i="' + i + '">' + escHtml(n) + '</div>'
                ).join('');
                autoList.classList.add('open');
                autoList.querySelectorAll('.autocomplete-item').forEach(el => {
                    el.addEventListener('mousedown', function(e){
                        e.preventDefault(); // prevent blur
                        searchInput.value = currentSuggestions[+this.dataset.i];
                        closeAuto();
                        fetchCards();
                    });
                });
            })
            .catch(console.error);
    }

    function closeAuto() {
        autoList.classList.remove('open');
        autoList.innerHTML = '';
        currentSuggestions = [];
        selectedIndex = -1;
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Search input events ───────────────────────────────────────────────
    searchInput.addEventListener('input', function(){
        const val = this.value.trim();
        clearTimeout(debounceTimer);
        clearTimeout(sugTimer);

        sugTimer  = setTimeout(() => fetchSuggestions(val), 200);
        debounceTimer = setTimeout(() => fetchCards(), 450);
    });

    searchInput.addEventListener('keydown', function(e){
        const items = autoList.querySelectorAll('.autocomplete-item');
        if (e.key === 'ArrowDown') {
            if (!items.length) return;
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
            if (!items.length) return;
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
        } else if (e.key === 'Enter') {
            if (selectedIndex >= 0 && items.length) {
                e.preventDefault();
                searchInput.value = currentSuggestions[selectedIndex];
                closeAuto();
                fetchCards();
                return;
            }
            clearTimeout(debounceTimer);
            closeAuto();
            fetchCards();
            return;
        } else if (e.key === 'Escape') {
            closeAuto(); return;
        } else { return; }
        items.forEach((el, i) => el.classList.toggle('selected', i === selectedIndex));
    });

    searchInput.addEventListener('blur', function(){
        setTimeout(closeAuto, 150);
    });

    // ── Difficulty select — combine with current search ───────────────────
    diffSelect.addEventListener('change', function(){
        closeAuto();
        fetchCards(); // keeps whatever text is in search input
    });
})();
</script>
</body>
</html>
