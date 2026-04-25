<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';
$controller = new MarathonController();
$pController = new ParcoursController();

$search = $_GET['search'] ?? '';
$filterRegion = $_GET['region'] ?? '';

if ($search !== '') $marathons = $controller->rechercherMarathon($search);
elseif ($filterRegion !== '') $marathons = $controller->filtrerMarathon($filterRegion);
else $marathons = $controller->afficherMarathon();

$stats = $controller->statsNbMarathonsDispo();
$pStats = $pController->statsParcours();
$regions = $controller->getRegions();
$currentPage = 'catalogue';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Catalogue Marathons — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:radial-gradient(circle at top right,rgba(255,183,3,.15),transparent 30%),linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }

        /* HERO */
        .hero-strip { display:grid; grid-template-columns:1.92fr 0.48fr; gap:14px; margin-bottom:26px; }
        .hero-card { background:linear-gradient(135deg,#102a43,#0f766e); color:white; border-radius:28px; padding:24px; box-shadow:0 16px 32px rgba(16,42,67,.15); position:relative; overflow:hidden; min-height:180px; }
        .hero-card::after { content:'🏃'; position:absolute; right:18px; bottom:10px; font-size:5rem; opacity:.12; }
        .hero-card h1 { font-size:clamp(1.6rem,3.5vw,2.4rem); line-height:1.1; margin-bottom:10px; }
        .hero-card p { opacity:.9; line-height:1.6; }
        .hero-stats { display:grid; grid-template-rows:repeat(2,auto); gap:8px; }
        .mini-box { background:white; border-radius:18px; padding:10px; box-shadow:0 6px 14px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.06); min-height:98px; }
        .mini-box .stats-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .mini-cell { background:#f8fafc; border-radius:16px; padding:10px 8px; text-align:center; border:1px solid rgba(16,42,67,.06); }
        .mini-cell .val { font-size:1.45rem; font-weight:900; color:var(--teal); line-height:1; margin-bottom:4px; }
        .mini-cell .lbl { color:#627d98; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.02em; }
        .difficulty-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; }
        .diff-chip { background:#f8fafc; border-radius:14px; padding:8px 6px; color:var(--ink); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px; min-height:70px; border:1px solid rgba(16,42,67,.06); }
        .diff-chip span { font-size:1.15rem; font-weight:900; color:var(--teal); }
        .diff-chip small { font-size:0.65rem; text-transform:uppercase; letter-spacing:.08em; opacity:.85; color:#627d98; }
        .diff-chip.diff-facile, .diff-chip.diff-moyen, .diff-chip.diff-difficile { background:#f8fafc; color:var(--ink); }
        .mini-box .stats-row + .stats-row { margin-top:6px; }

        /* FILTRE */
        .filter-section { background:white; border-radius:20px; padding:20px 22px; margin-bottom:26px; box-shadow:0 7px 20px rgba(16,42,67,.08); }
        .filter-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .filter-bar input {
            border-radius:13px; border:1px solid #cbd5e1; padding:12px 16px;
            font:inherit; flex:2 1 260px; min-width:0;
        }
        .filter-bar select {
            border-radius:13px; border:1px solid #cbd5e1; padding:12px 16px;
            font:inherit; flex:1 1 220px; min-width:0; max-width:280px;
        }
        .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color:var(--teal); }

        /* TOOLBAR for organisateur/admin */
        .toolbar-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
        .btn-add { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#16a34a,#22c55e); color:white; border:none; border-radius:12px; padding:11px 20px; font-weight:700; font-size:0.9rem; cursor:pointer; text-decoration:none; transition:transform .15s,box-shadow .15s; box-shadow:0 4px 14px rgba(22,163,74,.3); }
        .btn-add:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(22,163,74,.4); }
        .btn-pdf { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#1a1a1a,#374151); color:white; border:none; border-radius:12px; padding:11px 20px; font-weight:700; font-size:0.9rem; cursor:pointer; text-decoration:none; transition:transform .15s,box-shadow .15s; box-shadow:0 4px 14px rgba(0,0,0,.25); }
        .btn-pdf:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(0,0,0,.35); }

        /* CATALOGUE */
        .section-title { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .section-title h2 { font-size:1.8rem; }
        .count-badge { background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:5px 14px; font-weight:700; font-size:.86rem; }
        .catalog { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:24px; }

        /* CARD */
        .card {
            background:rgba(255,255,255,.92); border:1px solid rgba(16,42,67,.08);
            border-radius:24px; box-shadow:0 14px 36px rgba(16,42,67,.08);
            overflow:hidden;
            transform:translateY(16px); opacity:0; animation:rise 0.6s ease forwards;
            transition:transform .22s, box-shadow .22s;
            text-decoration:none; color:inherit; display:block;
        }
        .card:nth-child(2){animation-delay:.07s} .card:nth-child(3){animation-delay:.14s}
        .card:nth-child(4){animation-delay:.21s} .card:nth-child(5){animation-delay:.28s}
        .card:hover { transform:translateY(-6px); box-shadow:0 28px 56px rgba(16,42,67,.15); }
        .card-img-wrap { position:relative; }
        .card img { width:100%; height:240px; object-fit:cover; display:block; }
        .card-id { position:absolute; top:12px; left:12px; background:rgba(16,42,67,.8); color:white; border-radius:8px; padding:5px 12px; font-size:0.82rem; font-weight:700; backdrop-filter:blur(6px); }
        .card-body { padding:20px; }
        .pill-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
        .pill { font-size:0.8rem; padding:5px 10px; border-radius:999px; background:rgba(15,118,110,.1); color:var(--teal); font-weight:700; }
        .pill-sun { background:rgba(255,183,3,.15); color:#92400e; }
        .card h3 { font-size:1.2rem; margin-bottom:10px; }
        .meta { font-size:0.9rem; color:#486581; display:grid; gap:5px; margin-bottom:14px; }
        .card-footer { display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid #e6edf3; }
        .price { font-size:1.2rem; font-weight:900; color:var(--coral); }
        .btn-detail { display:inline-block; padding:10px 16px; border-radius:11px; font-weight:700; background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; font-size:0.88rem; }

        /* CARD ACTIONS for organisateur/admin */
        .card-actions { display:flex; gap:8px; padding:0 20px 16px; }
        .btn-mod-card { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:10px 14px; border-radius:10px; font-weight:700; font-size:0.85rem; background:linear-gradient(135deg,#cbd5e1,#e2e8f0); color:#475569; text-decoration:none; transition:transform .15s,box-shadow .15s; border:none; cursor:pointer; box-shadow:0 3px 10px rgba(203,213,225,.4); }
        .btn-mod-card:hover { transform:translateY(-1px); box-shadow:0 5px 14px rgba(203,213,225,.5); }
        .btn-del-card { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:10px 14px; border-radius:10px; font-weight:700; font-size:0.85rem; background:linear-gradient(135deg,#dc2626,#ef4444); color:white; text-decoration:none; transition:transform .15s,box-shadow .15s; cursor:pointer; border:none; box-shadow:0 3px 10px rgba(220,38,38,.3); }
        .btn-del-card:hover { transform:translateY(-1px); box-shadow:0 5px 14px rgba(220,38,38,.4); }

        .empty-state { text-align:center; padding:60px 20px; color:#627d98; }
        @keyframes rise { to { opacity:1; transform:translateY(0); } }
        @media(max-width:768px){ .hero-strip{grid-template-columns:1fr;} }

        /* AUTOCOMPLETE */
        .search-wrap { position:relative; flex:2 1 260px; min-width:0; }
        .search-wrap input { width:100%; border-radius:13px; border:1px solid #cbd5e1; padding:12px 16px; font:inherit; }
        .search-wrap input:focus { outline:none; border-color:var(--teal); }
        .autocomplete-list { position:absolute; top:calc(100% + 4px); left:0; right:0; background:white; border:1px solid #cbd5e1; border-radius:13px; box-shadow:0 8px 20px rgba(16,42,67,.12); z-index:999; max-height:220px; overflow-y:auto; display:none; }
        .autocomplete-list.open { display:block; }
        .autocomplete-item { padding:10px 16px; cursor:pointer; font-size:0.93rem; border-bottom:1px solid #f1f5f9; }
        .autocomplete-item:last-child { border-bottom:none; }
        .autocomplete-item:hover, .autocomplete-item.selected { background:#f0fdf9; color:var(--teal); font-weight:700; }

        /* CONFIRM MODAL */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(16,42,67,.5); z-index:3000; align-items:center; justify-content:center; backdrop-filter:blur(4px); }
        .modal-overlay.open { display:flex; }
        .modal-box { background:white; border-radius:24px; padding:36px 32px; width:min(420px,calc(100% - 32px)); text-align:center; box-shadow:0 24px 60px rgba(16,42,67,.2); }
        .modal-icon { font-size:3.5rem; margin-bottom:16px; }
        .modal-box h3 { font-size:1.4rem; margin-bottom:8px; }
        .modal-box p { color:#627d98; margin-bottom:24px; line-height:1.6; }
        .modal-btns { display:flex; gap:12px; justify-content:center; }
        .btn-confirm-del { background:linear-gradient(135deg,#dc2626,#ef4444); color:white; border:none; border-radius:12px; padding:12px 28px; font-weight:700; font-size:1rem; cursor:pointer; transition:transform .15s; }
        .btn-confirm-del:hover { transform:translateY(-1px); }
        .btn-cancel { background:#f1f5f9; color:#475569; border:none; border-radius:12px; padding:12px 28px; font-weight:700; font-size:1rem; cursor:pointer; }
        .btn-cancel:hover { background:#e2e8f0; }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">

    <section class="hero-strip">
        <div class="hero-card">
            <h1>Catalogue des Marathons</h1>
            <p>Explorez tous les événements de course en Tunisie.</p>
        </div>
        <div class="hero-stats">
            <div class="mini-box">
                <div class="stats-row">
                    <div class="mini-cell">
                        <div class="val"><?php echo count($marathons); ?></div>
                        <div class="lbl">Marathons</div>
                    </div>
                    <div class="mini-cell">
                        <div class="val"><?php echo number_format((float)($stats['total_places']??0)); ?></div>
                        <div class="lbl">Places</div>
                    </div>
                </div>
            </div>
            <div class="mini-box">
                <div class="stats-row">
                    <div class="mini-cell">
                        <div class="val"><?php echo $pStats['total'] ?? 0; ?></div>
                        <div class="lbl">Parcours</div>
                    </div>
                    <div class="mini-cell">
                        <div class="difficulty-grid">
                            <div class="diff-chip diff-facile">
                                <span><?php echo $pStats['facile'] ?? 0; ?></span>
                                <small>Facile</small>
                            </div>
                            <div class="diff-chip diff-moyen">
                                <span><?php echo $pStats['moyen'] ?? 0; ?></span>
                                <small>Moyen</small>
                            </div>
                            <div class="diff-chip diff-difficile">
                                <span><?php echo $pStats['difficile'] ?? 0; ?></span>
                                <small>Difficile</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="filter-section">
        <div class="filter-bar" id="filterBar">
            <div class="search-wrap">
                <input type="text" id="searchInput" placeholder="🔍 Rechercher par nom..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                <div class="autocomplete-list" id="autocompleteList"></div>
            </div>
            <select id="regionSelect" style="border-radius:13px;border:1px solid #cbd5e1;padding:12px 16px;font:inherit;flex:1 1 220px;min-width:0;max-width:280px;">
                <option value="">🌍 Toutes les régions</option>
                <?php foreach ($regions as $r): ?>
                    <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $filterRegion===$r?'selected':''; ?>><?php echo htmlspecialchars($r); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="toolbar-row">
        <div style="display:flex;align-items:center;gap:12px;">
            <h2 style="font-size:1.8rem;">Tous les Marathons</h2>
            <span class="count-badge" id="countBadge"><?php echo count($marathons); ?> résultats</span>
        </div>
        <div style="display:flex;gap:8px;">
            <?php if ($role === 'organisateur'): ?>
                <a href="marathon/addMarathon.php" class="btn-add">➕ Ajouter un marathon</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="catalogWrapper">
    <?php if (empty($marathons)): ?>
        <div class="empty-state">
            <div style="font-size:3rem;margin-bottom:14px;">🏃</div>
            <h3>Aucun marathon trouvé</h3>
        </div>
    <?php else: ?>
    <section class="catalog" id="marathonGrid">
        <?php foreach ($marathons as $m): ?>
        <div class="card" style="cursor:default;">
            <a href="detailMarathon.php?id=<?php echo $m['id_marathon']; ?>" style="text-decoration:none;color:inherit;display:block;">
                <div class="card-img-wrap">
                    <img src="images/hero_runner.png" alt="<?php echo htmlspecialchars($m['nom_marathon']); ?>" onerror="this.src='images/img1.svg'">
                    <span class="card-id">#<?php echo $m['id_marathon']; ?></span>
                </div>
                <div class="card-body">
                    <div class="pill-row">
                        <span class="pill">📍 <?php echo htmlspecialchars($m['region_marathon']); ?></span>
                        <span class="pill pill-sun"><?php echo $m['nb_places_dispo']>0?'✅ '.$m['nb_places_dispo'].' places':'❌ Complet'; ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($m['nom_marathon']); ?></h3>
                    <div class="meta">
                        <span>👤 <?php echo htmlspecialchars($m['organisateur_marathon']); ?></span>
                        <span>📅 <?php echo date('d/m/Y',strtotime($m['date_marathon'])); ?></span>
                    </div>
                    <div class="card-footer">
                        <span class="price"><?php echo number_format($m['prix_marathon'],2); ?> TND</span>
                        <span class="btn-detail">Voir détail →</span>
                    </div>
                </div>
            </a>
            <?php if ($role === 'organisateur' || $role === 'admin'): ?>
            <div class="card-actions" style="padding:12px;border-top:1px solid #e5e7eb;display:flex;gap:8px;">
                <?php if ($role === 'organisateur'): ?>
                <a href="marathon/updateMarathon.php?id=<?php echo $m['id_marathon']; ?>" class="btn-mod-card">✏️ Modifier</a>
                <?php endif; ?>
                <button class="btn-del-card" style="flex:1;padding:8px;font-size:0.85rem;" onclick="confirmDelete(<?php echo $m['id_marathon']; ?>, '<?php echo addslashes($m['nom_marathon']); ?>')">🗑️ Supprimer</button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
    </div><!-- end catalogWrapper -->

    <div style="height:40px;"></div>
</div>

<!-- CONFIRM DELETE MODAL -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <h3>Confirmation</h3>
        <p id="deleteModalText">Supprimer ce marathon ?</p>
        <div class="modal-btns">
            <button class="btn-confirm-del" id="confirmDelBtn">Oui, supprimer</button>
            <button class="btn-cancel" onclick="closeDeleteModal()">Annuler</button>
        </div>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script>
// ── AJAX search + autocomplete for marathons ──────────────────────────────
(function(){
    const searchInput    = document.getElementById('searchInput');
    const regionSelect   = document.getElementById('regionSelect');
    const autoList       = document.getElementById('autocompleteList');
    const catalogWrapper = document.getElementById('catalogWrapper');
    const countBadge     = document.getElementById('countBadge');

    let debounceTimer = null;
    let selectedIndex = -1;
    let currentSuggestions = [];

    // ── Fetch cards (AJAX) ────────────────────────────────────────────────
    function fetchCards() {
        const search = searchInput.value.trim();
        const region = regionSelect.value;
        fetch('search_marathons.php?mode=cards&search=' + encodeURIComponent(search) + '&region=' + encodeURIComponent(region))
            .then(r => r.json())
            .then(data => {
                catalogWrapper.innerHTML = data.html;
                countBadge.textContent  = data.count + ' résultat' + (data.count !== 1 ? 's' : '');
            })
            .catch(console.error);
    }

    // ── Fetch suggestions (autocomplete) ─────────────────────────────────
    function fetchSuggestions(val) {
        if (val.length === 0) { closeAuto(); return; }
        fetch('search_marathons.php?mode=suggestions&search=' + encodeURIComponent(val))
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
                        e.preventDefault(); // prevent blur before click
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

    // ── Search input events ────────────────────────────────────────────────
    searchInput.addEventListener('input', function(){
        const val = this.value.trim();
        clearTimeout(debounceTimer);

        // Suggestions: fast (200ms)
        clearTimeout(searchInput._sugTimer);
        searchInput._sugTimer = setTimeout(() => fetchSuggestions(val), 200);

        // Cards: slightly slower (450ms) to avoid too many requests
        debounceTimer = setTimeout(() => {
            fetchCards();
        }, 450);
    });

    // Keyboard navigation for autocomplete
    searchInput.addEventListener('keydown', function(e){
        const items = autoList.querySelectorAll('.autocomplete-item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
        } else if (e.key === 'Enter') {
            if (selectedIndex >= 0) {
                e.preventDefault();
                searchInput.value = currentSuggestions[selectedIndex];
                closeAuto();
                fetchCards();
                return;
            }
            // Enter with no selection → just search
            clearTimeout(debounceTimer);
            closeAuto();
            fetchCards();
            return;
        } else if (e.key === 'Escape') {
            closeAuto(); return;
        }
        items.forEach((el, i) => el.classList.toggle('selected', i === selectedIndex));
    });

    searchInput.addEventListener('blur', function(){
        // small delay so mousedown on autocomplete item fires first
        setTimeout(closeAuto, 150);
    });

    // ── Region select ─────────────────────────────────────────────────────
    regionSelect.addEventListener('change', function(){
        searchInput.value = ''; // clear text search when filtering by region
        closeAuto();
        fetchCards();
    });

    // ── Delete confirm modal ──────────────────────────────────────────────
    window.confirmDelete = function(id, nom) {
        document.getElementById('deleteModalText').textContent = 'Supprimer le marathon "' + nom + '" ?';
        document.getElementById('confirmDelBtn').onclick = function() {
            window.location.href = 'marathon/deleteMarathon.php?id=' + id + '&redirect=front';
        };
        document.getElementById('deleteModal').classList.add('open');
    };
    window.closeDeleteModal = function() {
        document.getElementById('deleteModal').classList.remove('open');
    };
    document.getElementById('deleteModal').addEventListener('click', function(e){
        if (e.target === this) closeDeleteModal();
    });
})();
</script>
</body>
</html>
