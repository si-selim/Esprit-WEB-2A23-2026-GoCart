<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/ParcoursController.php';
$controller = new ParcoursController();

if (isset($_GET['delete'])) {
    $controller->supprimerParcours((int)$_GET['delete']);
    header('Location: listParcours.php');
    exit;
}

$searchNom = $_GET['nom'] ?? '';
$filterDiff = $_GET['difficulte'] ?? '';

if ($searchNom !== '') {
    $parcours = $controller->rechercherParcoursParNom($searchNom);
} elseif ($filterDiff !== '') {
    $parcours = $controller->filtrerParcours($filterDiff);
} else {
    $parcours = $controller->afficherParcours();
}

$stats = $controller->statsParcours();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Back Office — Parcours | BarchaThon</title>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --card:#fff; --muted:#627d98; --coral:#e76f51; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fefaf0,var(--bg)); }
        .layout { min-height:100vh; display:grid; grid-template-columns:270px 1fr; }
        .sidebar { background:linear-gradient(180deg,#0b2032,#12314a); color:#fff; padding:24px 18px; position:sticky; top:0; height:100vh; display:flex; flex-direction:column; gap:20px; overflow-y:auto; }
        .brand { display:flex; flex-direction:column; align-items:center; gap:8px; padding-bottom:18px; border-bottom:1px solid rgba(255,255,255,.12); }
        .brand img { width:72px; border-radius:14px; }
        .brand-name { font-weight:900; font-size:1rem; }
        .brand small { color:rgba(255,255,255,.6); font-size:0.72rem; }
        .side-nav { display:grid; gap:8px; }
        .side-link { text-decoration:none; color:#fff; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.05); border-radius:14px; padding:11px 14px; font-weight:700; font-size:0.9rem; transition:background .2s; }
        .side-link:hover { background:rgba(255,255,255,.12); }
        .side-link.active { background:rgba(15,118,110,.35); border-color:var(--teal); }
        .content { padding:26px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:22px; flex-wrap:wrap; }
        .head h1 { margin:0; font-size:2rem; }
        .muted { color:var(--muted); font-size:0.93rem; margin-top:4px; }
        .btn { text-decoration:none; padding:10px 15px; border-radius:12px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:7px; font-size:0.9rem; }
        .btn-primary { background:var(--teal); color:#fff; }
        .btn-warning { background:var(--sun); color:var(--ink); }
        .btn-danger { background:var(--coral); color:#fff; }
        .btn-pdf { background:#1a1a2e; color:#fff; }
        .stats-grid { display:grid; gap:14px; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); margin-bottom:22px; }
        .stat-card { background:var(--card); border-radius:18px; padding:18px; box-shadow:0 8px 22px rgba(16,42,67,.07); border:1px solid rgba(16,42,67,.06); text-align:center; }
        .stat-val { font-size:1.9rem; font-weight:900; color:var(--teal); }
        .stat-lbl { color:var(--muted); font-size:0.83rem; margin-top:3px; }
        .section-card { background:var(--card); border-radius:22px; padding:20px; box-shadow:0 10px 28px rgba(16,42,67,.07); border:1px solid rgba(16,42,67,.07); margin-bottom:18px; }
        .section-title { margin:0 0 14px; font-size:1.3rem; font-weight:800; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; }
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .filter-bar input, .filter-bar select { border-radius:11px; border:1px solid #cbd5e1; padding:9px 13px; font:inherit; flex:1; min-width:160px; }
        .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color:var(--teal); }
        .table-shell { overflow:auto; }
        table { width:100%; min-width:760px; border-collapse:collapse; }
        th, td { padding:12px 10px; text-align:left; border-bottom:1px solid #e6edf3; vertical-align:middle; }
        th { background:#102a43; color:#fff; font-size:0.88rem; }
        tr:hover td { background:#f8fafc; }
        .table-actions { display:flex; gap:7px; }
        .tag { display:inline-block; padding:4px 10px; border-radius:999px; background:rgba(15,118,110,.1); color:var(--teal); font-weight:700; font-size:.8rem; }
        .tag-easy { background:rgba(16,185,129,.1); color:#059669; }
        .tag-medium { background:rgba(245,158,11,.1); color:#d97706; }
        .tag-hard { background:rgba(231,111,81,.1); color:var(--coral); }
        @media(max-width:960px){ .layout{grid-template-columns:1fr;} .sidebar{display:none;} }
        /* AUTOCOMPLETE */
        .search-wrap { position:relative; flex:1; min-width:160px; }
        .search-wrap input { width:100%; border-radius:11px; border:1px solid #cbd5e1; padding:9px 13px; font:inherit; }
        .search-wrap input:focus { outline:none; border-color:var(--teal); }
        .autocomplete-list { position:absolute; top:calc(100% + 4px); left:0; right:0; background:white; border:1px solid #cbd5e1; border-radius:11px; box-shadow:0 8px 20px rgba(16,42,67,.12); z-index:999; max-height:200px; overflow-y:auto; display:none; }
        .autocomplete-list.open { display:block; }
        .autocomplete-item { padding:9px 13px; cursor:pointer; font-size:0.9rem; border-bottom:1px solid #f1f5f9; }
        .autocomplete-item:last-child { border-bottom:none; }
        .autocomplete-item:hover, .autocomplete-item.selected { background:#f0fdf9; color:var(--teal); font-weight:700; }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <img src="../../FrontOffice/images/logobarchathon.jpg" alt="BarchaThon">
            <div class="brand-name">BarchaThon</div>
            <small>Back Office</small>
        </div>
        <nav class="side-nav">
            <a class="side-link" href="../marathon/listMarathons.php">🏃 Marathons</a>
            <a class="side-link" href="../marathon/addMarathon.php">➕ Ajouter Marathon</a>
            <a class="side-link active" href="listParcours.php">🗺️ Parcours</a>
            <a class="side-link" href="addParcours.php">➕ Ajouter Parcours</a>
            <a class="side-link" href="../../FrontOffice/accueil.php">↩ Retour</a>
        </nav>
    </aside>

    <main class="content">
        <div class="head">
            <div>
                <h1>🗺️ Gestion des Parcours</h1>
                <div class="muted">Ajouter, modifier, supprimer et filtrer les parcours.</div>
            </div>
            <a class="btn btn-pdf" href="#">📄 Exporter PDF</a>
        </div>

        <!-- Stats : seulement Total + Faciles + Moyens + Difficiles (pas distance moyenne) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-val"><?php echo (int)($stats['total'] ?? 0); ?></div>
                <div class="stat-lbl">Total Parcours</div>
            </div>
            <div class="stat-card" style="border-top:3px solid #059669;">
                <div class="stat-val" style="color:#059669;"><?php echo (int)($stats['facile'] ?? 0); ?></div>
                <div class="stat-lbl">🟢 Faciles</div>
            </div>
            <div class="stat-card" style="border-top:3px solid #d97706;">
                <div class="stat-val" style="color:#d97706;"><?php echo (int)($stats['moyen'] ?? 0); ?></div>
                <div class="stat-lbl">🟡 Moyens</div>
            </div>
            <div class="stat-card" style="border-top:3px solid var(--coral);">
                <div class="stat-val" style="color:var(--coral);"><?php echo (int)($stats['difficile'] ?? 0); ?></div>
                <div class="stat-lbl">🔴 Difficiles</div>
            </div>
        </div>

        <!-- Filtre AUTO -->
        <div class="section-card">
            <div class="filter-bar" id="filterBar">
                <div class="search-wrap">
                    <input type="text" id="nomInput" placeholder="🔍 Rechercher par nom de parcours..." value="<?php echo htmlspecialchars($searchNom); ?>" autocomplete="off">
                    <div class="autocomplete-list" id="autocompleteList"></div>
                </div>
                <select id="diffSelect" style="border-radius:11px;border:1px solid #cbd5e1;padding:9px 13px;font:inherit;flex:1;min-width:160px;">
                    <option value="">Toutes les difficultés</option>
                    <option value="facile" <?php echo $filterDiff==='facile'?'selected':''; ?>>🟢 Facile</option>
                    <option value="moyen" <?php echo $filterDiff==='moyen'?'selected':''; ?>>🟡 Moyen</option>
                    <option value="difficile" <?php echo $filterDiff==='difficile'?'selected':''; ?>>🔴 Difficile</option>
                </select>
            </div>
        </div>

        <!-- Tableau -->
        <div class="section-card">
            <div class="section-title">
                <span>Liste des Parcours</span>
                <span class="tag" id="countBadge"><?php echo count($parcours); ?> résultats</span>
            </div>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom du Parcours</th>
                            <th>Point Départ</th>
                            <th>Point Arrivée</th>
                            <th>Distance (km)</th>
                            <th>Difficulté</th>
                            <th>Marathon</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="parcoursTableBody">
                        <?php if (empty($parcours)): ?>
                            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:28px;">Aucun parcours trouvé.</td></tr>
                        <?php else: ?>
                            <?php foreach ($parcours as $p):
                                $dc = ['facile'=>'tag-easy','moyen'=>'tag-medium','difficile'=>'tag-hard'][$p['difficulte']] ?? 'tag';
                                $di = ['facile'=>'🟢','moyen'=>'🟡','difficile'=>'🔴'][$p['difficulte']] ?? '';
                            ?>
                            <tr>
                                <td><strong>#<?php echo $p['id_parcours']; ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($p['nom_parcours']); ?></strong></td>
                                <td>📍 <?php echo htmlspecialchars($p['point_depart']); ?></td>
                                <td>🏁 <?php echo htmlspecialchars($p['point_arrivee']); ?></td>
                                <td><strong><?php echo number_format((float)$p['distance'],2); ?> km</strong></td>
                                <td><span class="tag <?php echo $dc; ?>"><?php echo $di.' '.htmlspecialchars($p['difficulte']); ?></span></td>
                                <td><span class="tag">🏃 <?php echo htmlspecialchars($p['nom_marathon']); ?></span></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-warning" href="updateParcours.php?id=<?php echo $p['id_parcours']; ?>">✏️ Modifier</a>
                                        <a class="btn btn-danger" href="?delete=<?php echo $p['id_parcours']; ?>" onclick="return confirm('Supprimer ce parcours ?')">🗑️ Supprimer</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script>
(function(){
    const nomInput    = document.getElementById('nomInput');
    const diffSelect  = document.getElementById('diffSelect');
    const tbody       = document.getElementById('parcoursTableBody');
    const countBadge  = document.getElementById('countBadge');
    const autoList    = document.getElementById('autocompleteList');

    let debounceTimer = null;
    let selectedIndex = -1;
    let currentSuggestions = [];

    // ── Fetch table rows (AJAX) ───────────────────────────────────────────
    function fetchRows() {
        const nom  = nomInput.value.trim();
        const diff = diffSelect.value;
        fetch('ajax_search_parcours.php?mode=rows&nom=' + encodeURIComponent(nom) + '&difficulte=' + encodeURIComponent(diff))
            .then(r => r.json())
            .then(data => {
                tbody.innerHTML = data.html;
                countBadge.textContent = data.count + ' résultat' + (data.count !== 1 ? 's' : '');
            })
            .catch(console.error);
    }

    // ── Fetch suggestions ─────────────────────────────────────────────────
    function fetchSuggestions(val) {
        if (val.length === 0) { closeAuto(); return; }
        fetch('ajax_search_parcours.php?mode=suggestions&nom=' + encodeURIComponent(val))
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
                        e.preventDefault();
                        nomInput.value = currentSuggestions[+this.dataset.i];
                        closeAuto();
                        fetchRows();
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

    // ── Input events ──────────────────────────────────────────────────────
    nomInput.addEventListener('input', function(){
        const val = this.value.trim();
        clearTimeout(debounceTimer);
        clearTimeout(nomInput._sugTimer);
        nomInput._sugTimer = setTimeout(() => fetchSuggestions(val), 200);
        debounceTimer = setTimeout(() => fetchRows(), 450);
    });

    nomInput.addEventListener('keydown', function(e){
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
                nomInput.value = currentSuggestions[selectedIndex];
                closeAuto();
                fetchRows();
                return;
            }
            clearTimeout(debounceTimer);
            closeAuto();
            fetchRows();
            return;
        } else if (e.key === 'Escape') {
            closeAuto(); return;
        }
        items.forEach((el, i) => el.classList.toggle('selected', i === selectedIndex));
    });

    nomInput.addEventListener('blur', function(){
        setTimeout(closeAuto, 150);
    });

    // ── Difficulty select ─────────────────────────────────────────────────
    diffSelect.addEventListener('change', function(){
        nomInput.value = ''; // vider la recherche texte lors du filtre
        closeAuto();
        fetchRows();
    });
})();
</script>
</body>
</html>
