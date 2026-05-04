<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
require_once __DIR__ . '/../../../Controller/standcontroller.php';
require_once __DIR__ . '/../../../Controller/ParcoursController.php';

$standController = new StandController();
$parcoursController = new ParcoursController();

$listStands = $standController->listStands();
$listParcours = $parcoursController->afficherParcours();

$uniquePositions = count(array_unique(array_column($listStands, 'position')));

$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
$currentPage = 'stands';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue des Stands | BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:radial-gradient(circle at top right,rgba(255,183,3,.15),transparent 30%),linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }

        /* HERO */
        .hero-strip { display:grid; grid-template-columns:1.92fr 0.48fr; gap:14px; margin-bottom:26px; }
        .hero-card { background:linear-gradient(135deg,#102a43,#0f766e); color:white; border-radius:28px; padding:24px; box-shadow:0 16px 32px rgba(16,42,67,.15); position:relative; overflow:hidden; min-height:180px; }
        .hero-card::after { content:'🏪'; position:absolute; right:18px; bottom:10px; font-size:5rem; opacity:.12; }
        .hero-card h1 { font-size:clamp(1.6rem,3.5vw,2.4rem); line-height:1.1; margin-bottom:10px; }
        .hero-card p { opacity:.9; line-height:1.6; }
        .hero-stats { display:grid; grid-template-rows:repeat(2,auto); gap:8px; }
        .mini-box { background:white; border-radius:18px; padding:10px; box-shadow:0 6px 14px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.06); min-height:98px; }
        .mini-box .stats-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .mini-cell { background:#f8fafc; border-radius:16px; padding:10px 8px; text-align:center; border:1px solid rgba(16,42,67,.06); }
        .mini-cell .val { font-size:1.45rem; font-weight:900; color:var(--teal); line-height:1; margin-bottom:4px; }
        .mini-cell .lbl { color:#627d98; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.02em; }

        /* FILTRE */
        .filter-section { background:white; border-radius:20px; padding:20px 22px; margin-bottom:26px; box-shadow:0 7px 20px rgba(16,42,67,.08); }
        .filter-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .filter-bar input {
            border-radius:13px; border:1px solid #cbd5e1; padding:12px 16px;
            font:inherit; width: 100%; box-sizing: border-box;
        }
        .filter-bar select {
            border-radius:13px; border:1px solid #cbd5e1; padding:12px 16px;
            font:inherit; flex: 0 0 240px; min-width:0;
        }
        .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color:var(--teal); }

        /* TOOLBAR */
        .toolbar-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
        .btn-add { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#16a34a,#22c55e); color:white; border:none; border-radius:12px; padding:11px 20px; font-weight:700; font-size:0.9rem; cursor:pointer; text-decoration:none; transition:transform .15s,box-shadow .15s; box-shadow:0 4px 14px rgba(22,163,74,.3); }
        .btn-add:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(22,163,74,.4); }

        /* CATALOGUE */
        .count-badge { background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:5px 14px; font-weight:700; font-size:.86rem; }
        .catalog { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:24px; }

        /* CARD */
        .card {
            background:rgba(255,255,255,.92); border:1px solid rgba(16,42,67,.08);
            border-radius:24px; box-shadow:0 14px 36px rgba(16,42,67,.08);
            overflow:hidden;
            transform:translateY(16px); opacity:0; animation:rise 0.6s ease forwards;
            transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration:none; color:inherit; display:block;
            position: relative;
        }
        .card:hover { 
            transform:translateY(-12px) scale(1.02); 
            box-shadow:0 30px 60px rgba(15,118,110,0.18);
            border-color: var(--teal);
        }
        .card-img-wrap { position:relative; overflow:hidden; }
        .card img { 
            width:100%; height:200px; object-fit:cover; display:block; 
            transition: transform 0.6s ease;
        }
        .card:hover img { transform: scale(1.1); }
        
        /* SHINE EFFECT ON HOVER */
        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%);
            transform: skewX(-25deg);
            transition: none;
            z-index: 1;
        }
        .card:hover::before {
            left: 150%;
            transition: all 0.7s ease;
        }
        .card-id { position:absolute; top:12px; left:12px; background:rgba(16,42,67,.8); color:white; border-radius:8px; padding:5px 12px; font-size:0.82rem; font-weight:700; backdrop-filter:blur(6px); }
        .card-body { padding:20px; }
        .pill-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
        .pill { font-size:0.8rem; padding:5px 10px; border-radius:999px; background:rgba(15,118,110,.1); color:var(--teal); font-weight:700; }
        .pill-sun { background:rgba(255,183,3,.15); color:#92400e; }
        .card h3 { font-size:1.2rem; margin-bottom:10px; }
        .meta { font-size:0.9rem; color:#486581; display:grid; gap:5px; margin-bottom:14px; }
        .description-cell { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .card-footer { display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid #e6edf3; }
        .btn-detail { display:inline-block; padding:10px 16px; border-radius:11px; font-weight:700; background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; font-size:0.88rem; }

        .card-actions { display:flex; gap:8px; padding:0 20px 16px; }
        .btn-mod-card { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:10px 14px; border-radius:10px; font-weight:700; font-size:0.85rem; background:linear-gradient(135deg,#cbd5e1,#e2e8f0); color:#475569; text-decoration:none; transition:transform .15s,box-shadow .15s; border:none; cursor:pointer; box-shadow:0 3px 10px rgba(203,213,225,.4); }
        .btn-mod-card:hover { transform:translateY(-1px); box-shadow:0 5px 14px rgba(203,213,225,.5); }
        .btn-del-card { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:10px 14px; border-radius:10px; font-weight:700; font-size:0.85rem; background:linear-gradient(135deg,#dc2626,#ef4444); color:white; text-decoration:none; transition:transform .15s,box-shadow .15s; cursor:pointer; border:none; box-shadow:0 3px 10px rgba(220,38,38,.3); }
        .btn-del-card:hover { transform:translateY(-1px); box-shadow:0 5px 14px rgba(220,38,38,.4); }

        .empty-state { text-align:center; padding:60px 20px; color:#627d98; }
        @keyframes rise { to { opacity:1; transform:translateY(0); } }
        @media(max-width:768px){ .hero-strip{grid-template-columns:1fr;} }

        /* AUTOCOMPLETE */
        .search-wrap { position:relative; flex: 1; min-width:0; }
        .autocomplete-list { position:absolute; top:calc(100% + 4px); left:0; right:0; background:white; border:1px solid #cbd5e1; border-radius:13px; box-shadow:0 8px 20px rgba(16,42,67,.12); z-index:999; max-height:220px; overflow-y:auto; display:none; }
        .autocomplete-list.open { display:block; }
        .autocomplete-item { padding:10px 16px; cursor:pointer; font-size:0.93rem; border-bottom:1px solid #f1f5f9; }
        .autocomplete-item:hover, .autocomplete-item.selected { background:#f0fdf9; color:var(--teal); font-weight:700; }

        /* MODAL */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(16,42,67,.5); z-index:3000; align-items:center; justify-content:center; backdrop-filter:blur(4px); }
        .modal-overlay.open { display:flex; }
        .modal-box { background:white; border-radius:24px; padding:36px 32px; width:min(420px,calc(100% - 32px)); text-align:center; box-shadow:0 24px 60px rgba(16,42,67,.2); }
        .btn-confirm-del { background:linear-gradient(135deg,#dc2626,#ef4444); color:white; border:none; border-radius:12px; padding:12px 28px; font-weight:700; font-size:1rem; cursor:pointer; }
        .btn-cancel { background:#f1f5f9; color:#475569; border:none; border-radius:12px; padding:12px 28px; font-weight:700; font-size:1rem; cursor:pointer; }

        /* DARK MODE */
        html[data-theme="dark"] body { background: radial-gradient(circle at top right,rgba(20,184,166,0.1),transparent 30%), #0f172a; color:#e2e8f0; }
        html[data-theme="dark"] .hero-card { background:linear-gradient(135deg,#020617,#0f766e); }
        html[data-theme="dark"] .mini-box { background:#1e293b; border-color:rgba(255,255,255,0.05); }
        html[data-theme="dark"] .mini-cell { background:#162032; border-color:rgba(255,255,255,0.05); }
        html[data-theme="dark"] .filter-section { background:#1e293b; }
        html[data-theme="dark"] .filter-bar input, html[data-theme="dark"] .filter-bar select { background:#162032; border-color:rgba(255,255,255,0.1); color:white; }
        html[data-theme="dark"] .card { background:rgba(30,41,59,.92); border-color:rgba(255,255,255,0.08); }
        html[data-theme="dark"] .card h3 { color:white; }
        html[data-theme="dark"] .meta { color:#94a3b8; }
        html[data-theme="dark"] .card-footer { border-top-color:rgba(255,255,255,0.08); }
        html[data-theme="dark"] .autocomplete-list { background:#1e293b; border-color:rgba(255,255,255,0.1); }
        html[data-theme="dark"] .autocomplete-item:hover { background:rgba(20,184,166,0.1); }
        html[data-theme="dark"] .modal-box { background:#1e293b; color:white; }
        html[data-theme="dark"] .btn-cancel { background:#162032; color:#94a3b8; }
    </style>
</head>
<body>
<?php require __DIR__ . '/../partials/topbar.php'; ?>
<div class="page">

    <section class="hero-strip">
        <div class="hero-card">
            <h1>Catalogue des Stands</h1>
            <p>Découvrez l'emplacement et les services de chaque stand sur nos parcours.</p>
        </div>
        <div class="hero-stats">
            <div class="mini-box">
                <div class="stats-row">
                    <div class="mini-cell">
                        <div class="val"><?php echo count($listStands); ?></div>
                        <div class="lbl">Stands</div>
                    </div>
                    <div class="mini-cell">
                        <div class="val"><?php echo $uniquePositions; ?></div>
                        <div class="lbl">Lieux</div>
                    </div>
                </div>
            </div>
            <div class="mini-box" style="display:flex; align-items:center; justify-content:center; color:var(--teal); font-weight:800; font-size:0.9rem; text-transform:uppercase;">
                <i class="fas fa-store" style="font-size:2rem; opacity:0.2; margin-right:10px;"></i> BarchaThon
            </div>
        </div>
    </section>

    <div class="filter-section">
        <div class="filter-bar">
            <div class="search-wrap">
                <input type="text" id="searchInput" placeholder="🔍 Rechercher un stand, un lieu..." autocomplete="off">
                <div class="autocomplete-list" id="autocompleteList"></div>
            </div>
            <select id="parcoursSelect">
                <option value="">🚩 Tous les parcours</option>
                <?php foreach ($listParcours as $p): ?>
                    <option value="<?php echo $p['id_parcours']; ?>">Parcours #<?php echo $p['id_parcours']; ?> - <?php echo htmlspecialchars($p['nom_parcours']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="toolbar-row">
        <div style="display:flex;align-items:center;gap:12px;">
            <h2 style="font-size:1.8rem;">Tous les Stands</h2>
            <span class="count-badge" id="countBadge"><?php echo count($listStands); ?> résultats</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <?php if (isOrganisateur()): ?>
            <select id="sortSelect" style="padding:11px 16px; border-radius:12px; border:1px solid #cbd5e1; outline:none; font-family:inherit; font-weight:600; color:#334155; background:#f8fafc; cursor:pointer;">
                <option value="">↕️ Trier par défaut</option>
                <option value="id_asc">ID Croissant</option>
                <option value="id_desc">ID Décroissant</option>
                <option value="nom_asc">Nom (A-Z)</option>
                <option value="nom_desc">Nom (Z-A)</option>
            </select>
            <?php endif; ?>
            <?php if (isOrganisateur()): ?>
                <a href="crud-stand.php" class="btn-add">➕ Ajouter un stand</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="catalogWrapper">
        <?php if (empty($listStands)): ?>
            <div class="empty-state">
                <div style="font-size:3rem;margin-bottom:14px;">🏪</div>
                <h3>Aucun stand enregistré</h3>
            </div>
        <?php else: ?>
            <section class="catalog" id="standGrid">
                <?php foreach ($listStands as $s): ?>
                    <div class="card" style="cursor:default;">
                        <div class="card-img-wrap">
                            <img src="../images/LOGO.jpg?v=2" alt="<?php echo htmlspecialchars($s['nom_stand']); ?>" onerror="this.src='../logo.png'">
                            <span class="card-id">#<?php echo $s['ID_stand']; ?></span>
                        </div>
                        <div class="card-body">
                            <div class="pill-row">
                                <span class="pill">📍 <?php echo htmlspecialchars($s['position']); ?></span>
                                <span class="pill pill-sun">🚩 Parcours #<?php echo $s['ID_parcours']; ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($s['nom_stand']); ?></h3>
                            <div class="meta">
                                <p class="description-cell"><?php echo htmlspecialchars($s['description']); ?></p>
                            </div>
                        </div>
                        <?php if ($role === 'organisateur' || $role === 'admin'): ?>
                            <div class="card-actions" style="padding:12px;border-top:1px solid #e5e7eb;display:flex;gap:8px;">
                                <a href="updateStand.php?id=<?php echo $s['ID_stand']; ?>" class="btn-mod-card">✏️ Modifier</a>
                                <button class="btn-del-card" style="flex:1;padding:8px;font-size:0.85rem;" onclick="confirmDelete(<?php echo $s['ID_stand']; ?>, '<?php echo addslashes($s['nom_stand']); ?>')">🗑️ Supprimer</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>

    <div style="height:40px;"></div>
</div>

<!-- DELETE MODAL -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div style="font-size:3.5rem; margin-bottom:16px;">🗑️</div>
        <h3>Confirmation</h3>
        <p id="deleteModalText" style="color:#627d98; margin-bottom:24px; line-height:1.6;">Supprimer ce stand ?</p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button class="btn-confirm-del" id="confirmDelBtn">Oui, supprimer</button>
            <button class="btn-cancel" onclick="closeDeleteModal()">Annuler</button>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function(){
    const searchInput    = document.getElementById('searchInput');
    const parcoursSelect = document.getElementById('parcoursSelect');
    const autoList       = document.getElementById('autocompleteList');
    const catalogWrapper = document.getElementById('catalogWrapper');
    const countBadge     = document.getElementById('countBadge');

    let debounceTimer = null;
    let selectedIndex = -1;
    let currentSuggestions = [];
    let userLat = null;
    let userLon = null;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                userLat = pos.coords.latitude;
                userLon = pos.coords.longitude;
                fetchCards();
            },
            function(err) {
                console.warn("Géolocalisation non disponible :", err.message);
            },
            { timeout: 7000, enableHighAccuracy: true, maximumAge: 0 }
        );
    }

    function fetchCards() {
        const search = searchInput.value.trim();
        const parcours = parcoursSelect.value;
        const sortSelect = document.getElementById('sortSelect');
        const sort = sortSelect ? sortSelect.value : '';
        
        let url = 'search_stands.php?mode=cards&search=' + encodeURIComponent(search) + '&parcours=' + encodeURIComponent(parcours) + '&sort=' + encodeURIComponent(sort);
        if (userLat !== null && userLon !== null) {
            url += '&lat=' + userLat + '&lon=' + userLon;
        }
        
        fetch(url)
            .then(r => r.json())
            .then(data => {
                catalogWrapper.innerHTML = data.html;
                countBadge.textContent  = data.count + ' résultat' + (data.count !== 1 ? 's' : '');
            })
            .catch(console.error);
    }

    function fetchSuggestions(val) {
        if (val.length === 0) { closeAuto(); return; }
        fetch('search_stands.php?mode=suggestions&search=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(names => {
                currentSuggestions = names;
                selectedIndex = -1;
                if (names.length === 0) { closeAuto(); return; }
                autoList.innerHTML = names.map((n, i) =>
                    '<div class="autocomplete-item" data-i="' + i + '">' + n + '</div>'
                ).join('');
                autoList.classList.add('open');

                autoList.querySelectorAll('.autocomplete-item').forEach(el => {
                    el.addEventListener('mousedown', function(e){
                        e.preventDefault();
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

    searchInput.addEventListener('input', function(){
        const val = this.value.trim();
        clearTimeout(debounceTimer);
        clearTimeout(searchInput._sugTimer);
        searchInput._sugTimer = setTimeout(() => fetchSuggestions(val), 200);
        debounceTimer = setTimeout(fetchCards, 450);
    });

    parcoursSelect.addEventListener('change', () => {
        searchInput.value = '';
        closeAuto();
        fetchCards();
    });

    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            fetchCards();
        });
    }

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
            }
        } else if (e.key === 'Escape') {
            closeAuto();
        }
        items.forEach((el, i) => el.classList.toggle('selected', i === selectedIndex));
    });

    searchInput.addEventListener('blur', () => setTimeout(closeAuto, 150));

    window.confirmDelete = function(id, nom) {
        document.getElementById('deleteModalText').textContent = 'Supprimer le stand "' + nom + '" ?';
        document.getElementById('confirmDelBtn').onclick = function() {
            window.location.href = 'deleteStand.php?id=' + id + '&redirect=front';
        };
        document.getElementById('deleteModal').classList.add('open');
    };
    window.closeDeleteModal = function() {
        document.getElementById('deleteModal').classList.remove('open');
    };
})();
</script>
</body>
</html>
