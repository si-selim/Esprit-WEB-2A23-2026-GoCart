<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

if (!isAdmin()) { header('Location: ../FrontOffice/accueil.php'); exit; }

$mCtrl = new MarathonController();
$pCtrl = new ParcoursController();

$id_marathon = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_marathon <= 0) { header('Location: dashboard.php?tab=marathons'); exit; }

if (isset($_GET['del_p'])) {
    $pCtrl->supprimerParcours((int)$_GET['del_p']);
    header('Location: voirdetailsparcours.php?id=' . $id_marathon);
    exit;
}

$marathon = $mCtrl->showMarathon($id_marathon);
if (!$marathon) { header('Location: dashboard.php?tab=marathons'); exit; }

$searchP    = isset($_GET['searchP'])    ? trim($_GET['searchP'])    : '';
$filterDiff = isset($_GET['difficulte']) ? trim($_GET['difficulte']) : '';

$tousParcours = $pCtrl->afficherParcours();

$parcoursDeMarathon = array();
$statsTotal = 0; $statsFacile = 0; $statsMoyen = 0; $statsDifficile = 0;
foreach ($tousParcours as $p) {
    if ((int)$p['id_marathon'] === $id_marathon) {
        $statsTotal++;
        $d = strtolower(trim($p['difficulte'] ?? ''));
        if ($d === 'facile')         $statsFacile++;
        elseif ($d === 'moyen')      $statsMoyen++;
        elseif ($d === 'difficile')  $statsDifficile++;
        $parcoursDeMarathon[] = $p;
    }
}

if ($searchP !== '') {
    $parcoursAffich = array();
    foreach ($parcoursDeMarathon as $p) {
        if (stripos($p['nom_parcours'], $searchP) !== false
            || stripos($p['point_depart'], $searchP) !== false
            || stripos($p['point_arrivee'], $searchP) !== false) {
            $parcoursAffich[] = $p;
        }
    }
} elseif ($filterDiff !== '') {
    $parcoursAffich = array();
    foreach ($parcoursDeMarathon as $p) {
        if (strtolower(trim($p['difficulte'] ?? '')) === strtolower(trim($filterDiff))) {
            $parcoursAffich[] = $p;
        }
    }
} else {
    $parcoursAffich = $parcoursDeMarathon;
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Details Marathon - BarchaThon</title>
<style>
    :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --card:#fff; --muted:#627d98; --coral:#e76f51; --line:#d9e2ec; }
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#F0F4F8,var(--bg)); }

    /* ── LAYOUT / SIDEBAR ── */
    .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
    .sidebar {
        background:linear-gradient(180deg,#0b2032 0%,#12314a 100%);
        color:#fff; padding:28px 22px; position:sticky; top:0;
        height:100vh; display:flex; flex-direction:column; gap:24px; overflow-y:auto;
    }
    .brand { display:grid; gap:10px; padding-bottom:20px; border-bottom:1px solid rgba(255,255,255,.14); }
    .brand-badge { width:52px; height:52px; border-radius:18px; object-fit:cover; }
    .brand small, .side-note { color:rgba(255,255,255,.72); font-size:.8rem; }
    .side-nav { display:grid; gap:10px; }
    .side-link {
        text-decoration:none; color:#fff;
        border:1px solid rgba(255,255,255,.1);
        background:rgba(255,255,255,.05);
        border-radius:16px; padding:12px 14px; font-weight:700; font-size:.93rem;
        transition:background .18s;
    }
    .side-link:hover { background:rgba(255,255,255,.12); }
    .side-link.active { background:linear-gradient(135deg,var(--teal),#14b8a6); border-color:transparent; }
    .content { padding:28px; }

    /* ── HEAD / BUTTONS ── */
    .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
    .head h1 { margin:0; font-size:2rem; }
    .muted { color:var(--muted); font-size:.9rem; }
    .btn { text-decoration:none; padding:10px 16px; border-radius:12px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:7px; font-size:.9rem; }
    .btn-primary { background:var(--teal); color:#fff; }
    .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.1); }
    .btn-danger { background:var(--coral); color:#fff; }
    .btn-sm { padding:8px 14px !important; font-size:.85rem !important; }

    /* ── STATS ── */
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(148px,1fr)); gap:14px; margin-bottom:20px; }
    .stat-card { background:white; border-radius:18px; padding:10px 12px; box-shadow:0 8px 22px rgba(16,42,67,.07); border:1px solid rgba(16,42,67,.06); text-align:center; }
    .stat-val { font-size:1.9rem; font-weight:900; color:var(--teal); }
    .stat-lbl { color:#627d98; font-size:.82rem; margin-top:4px; }

    /* ── PANELS / TABLES ── */
    .panel { background:white; border-radius:20px; padding:20px; box-shadow:0 8px 26px rgba(16,42,67,.07); border:1px solid rgba(16,42,67,.07); margin-bottom:14px; }
    .filter-bar { display:flex; gap:10px; flex-wrap:wrap; }
    .filter-bar input,.filter-bar select { border-radius:11px; border:1px solid #cbd5e1; padding:9px 13px; font:inherit; flex:1; min-width:160px; font-size:.88rem; background:white; }
    .filter-bar input:focus,.filter-bar select:focus { outline:none; border-color:var(--teal); }
    .section-card { background:white; border-radius:20px; padding:24px; box-shadow:0 8px 26px rgba(16,42,67,.07); border:1px solid rgba(16,42,67,.07); margin-bottom:20px; }
    .section-title { font-size:1.1rem; font-weight:800; color:#102a43; }
    .table-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .table-shell { max-height:520px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:12px; background:white; }
    .table-shell table { width:100%; border-collapse:collapse; }
    .table-shell thead { position:sticky; top:0; background:white; z-index:1; }
    .table-shell thead th { background:#f8fafc; border-bottom:2px solid #e2e8f0; padding:12px 16px; text-align:left; font-weight:600; font-size:.9rem; color:#374151; }
    .table-shell tbody td { padding:12px 16px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .table-shell tbody tr:hover { background:#f8fafc; }
    .tag { display:inline-block; padding:5px 10px; border-radius:999px; background:rgba(15,118,110,.1); color:var(--teal); font-weight:700; font-size:.82rem; }
    .tag-easy { background:rgba(16,185,129,.08); color:#059669; }
    .tag-med  { background:rgba(245,158,11,.08);  color:#d97706; }
    .tag-hard { background:rgba(239,68,68,.08);   color:#dc2626; }

    /* ── MODAL ── */
    .overlay { display:none; position:fixed; inset:0; background:rgba(16,42,67,.5); z-index:9999; align-items:center; justify-content:center; }
    .overlay.show { display:flex; }
    .modal-box { background:#fff; border-radius:22px; padding:32px 28px; max-width:420px; width:90%; text-align:center; }
    .modal-box h3 { font-size:1.15rem; margin-bottom:10px; }
    .modal-box p { color:#64748b; margin-bottom:22px; }
    .modal-actions { display:flex; gap:12px; justify-content:center; }

    @media(max-width:960px){ .layout{grid-template-columns:1fr;} .sidebar{position:relative;height:auto;} }
</style>
</head>
<body>
<div class="layout">

<?php $activeTab = 'marathons'; require __DIR__ . '/partials/sidebar.php'; ?>

<main class="content">

<div class="head">
    <div>
        <a href="dashboard.php?tab=marathons" class="btn btn-secondary btn-sm" style="margin-bottom:12px;">&larr; Retour aux marathons</a>
        <h1>Gestion des parcours de <?php echo htmlspecialchars($marathon['nom_marathon']); ?></h1>
        <div class="muted" style="margin-top:4px;font-size:0.9rem;color:#64748b;">Marathon <strong>#<?php echo $id_marathon; ?></strong> &mdash; <?php echo htmlspecialchars($marathon['nom_marathon']); ?> &mdash; consultation et suppression uniquement.</div>
    </div>
</div>

<!--
<div class="marathon-info-card">
    <img src="../FrontOffice/<?php echo htmlspecialchars($marathon['image_marathon']); ?>" onerror="this.style.display='none'" alt="">
    <div class="mic-body">
        <h2><?php echo htmlspecialchars($marathon['nom_marathon']); ?></h2>
    </div>
</div>
-->

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-val"><?php echo $statsTotal; ?></div>
        <div class="stat-lbl">Total Parcours</div>
    </div>
    <div class="stat-card" style="border-top-color:#059669;">
        <div class="stat-val" style="color:#059669;"><?php echo $statsFacile; ?></div>
        <div class="stat-lbl">Faciles</div>
    </div>
    <div class="stat-card" style="border-top-color:#d97706;">
        <div class="stat-val" style="color:#d97706;"><?php echo $statsMoyen; ?></div>
        <div class="stat-lbl">Moyens</div>
    </div>
    <div class="stat-card" style="border-top-color:#dc2626;">
        <div class="stat-val" style="color:#dc2626;"><?php echo $statsDifficile; ?></div>
        <div class="stat-lbl">Difficiles</div>
    </div>
</div>

   <div class="panel">
        <div class="filter-bar">
            <input type="text" id="sP" placeholder="🔍 Rechercher par nom de parcours..." value="" autocomplete="off" style="flex:2 1 220px; height:44px;">
            <select id="dP" style="flex:1 1 180px; height:44px;">
                <option value="">Toutes les difficultés</option>
                <option value="facile">🟢 Facile</option>
                <option value="moyen">🟡 Moyen</option>
                <option value="difficile">🔴 Difficile</option>
            </select>
        </div>
    </div>

<section class="section-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 class="section-title" style="margin:0;">Liste des Parcours</h2>
        <span class="tag" id="parcoursCountBadge"><?php echo count($parcoursAffich); ?> résultat(s)</span>
    </div>
    <div class="table-shell">
        <table id="parcoursTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du Parcours</th>
                    <th>Heure Départ</th>
                    <th>Point Depart</th>
                    <th>Point Arrivee</th>
                    <th>Distance (km)</th>
                    <th>Difficulte</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($parcoursAffich)): ?>
                <tr><td colspan="8" style="text-align:center;padding:30px;color:#64748b;">Aucun parcours trouve pour ce marathon.</td></tr>
            <?php else: ?>
                <?php foreach ($parcoursAffich as $p2): ?>
                <?php
                    $diff = strtolower(trim($p2['difficulte'] ?? ''));
                    $dc = '';
                    if ($diff === 'facile')        $dc = 'tag-easy';
                    elseif ($diff === 'moyen')     $dc = 'tag-med';
                    elseif ($diff === 'difficile') $dc = 'tag-hard';
                    $diffLabel = ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$diff] ?? (!empty($p2['difficulte']) ? htmlspecialchars($p2['difficulte']) : '—');
                ?>
                <tr>
                    <td><strong><?php echo (int)$p2['id_parcours']; ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($p2['nom_parcours']); ?></strong></td>
                    <td><?php echo !empty($p2['heure_depart']) ? '<strong>'.htmlspecialchars(substr($p2['heure_depart'],0,5)).'</strong>' : '<span style="color:#94a3b8;">—</span>'; ?></td>
                    <td><?php echo htmlspecialchars($p2['point_depart']); ?></td>
                    <td><?php echo htmlspecialchars($p2['point_arrivee']); ?></td>
                    <td><strong><?php echo number_format((float)$p2['distance'], 2); ?> km</strong></td>
                    <td><span class="tag <?php echo $dc; ?>"><?php echo $diffLabel; ?></span></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-danger btn-sm"
                                onclick="askDel(<?php echo (int)$p2['id_parcours']; ?>, '<?php echo htmlspecialchars($p2['nom_parcours'], ENT_QUOTES); ?>')">
                                Supprimer
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

</main>
</div>

<div class="overlay" id="confirmOverlay">
    <div class="modal-box">
        <h3>Confirmer la suppression</h3>
        <p id="confirm-msg">Supprimer ce parcours ?</p>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="document.getElementById('confirmOverlay').classList.remove('show')">Annuler</button>
            <button class="btn btn-danger" id="confirm-btn">Supprimer</button>
        </div>
    </div>
</div>

<script>
function askDel(id, nom) {
    document.getElementById('confirm-msg').textContent = 'Supprimer le parcours "' + nom + '" ?';
    document.getElementById('confirm-btn').onclick = function() {
        window.location.href = 'voirdetailsparcours.php?id=<?php echo (int)$id_marathon; ?>&del_p=' + id;
    };
    document.getElementById('confirmOverlay').classList.add('show');
}
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
});

// ── AJAX dynamic search ───────────────────────────────────────────────────
(function(){
    var sP    = document.getElementById('sP');
    var dP    = document.getElementById('dP');
    var tbody = document.querySelector('#parcoursTable tbody');
    var badge = document.getElementById('parcoursCountBadge');
    var MARATHON_ID = <?php echo (int)$id_marathon; ?>;
    var timer;

    function fetchRows() {
        var search = sP.value;
        var diff   = dP.value;
        fetch('ajax_search_parcours_marathon.php?id=' + MARATHON_ID
            + '&search=' + encodeURIComponent(search)
            + '&difficulte=' + encodeURIComponent(diff))
            .then(function(r){ return r.json(); })
            .then(function(data){
                tbody.innerHTML = data.html;
                if (badge) badge.textContent = data.count + ' résultat(s)';
            })
            .catch(console.error);
    }

    sP.addEventListener('input', function(){
        clearTimeout(timer);
        timer = setTimeout(fetchRows, 350);
    });
    dP.addEventListener('change', function(){
        sP.value = '';
        fetchRows();
    });
})();
</script>
</body>
</html>
