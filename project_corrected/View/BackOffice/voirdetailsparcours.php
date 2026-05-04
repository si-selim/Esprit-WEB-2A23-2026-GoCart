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
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.tag-easy{background:rgba(16,185,129,.08);color:#059669;border-color:rgba(16,185,129,.15);}
.tag-med{background:rgba(245,158,11,.08);color:#d97706;border-color:rgba(245,158,11,.15);}
.tag-hard{background:rgba(239,68,68,.08);color:#dc2626;border-color:rgba(239,68,68,.15);}
.table-actions{display:flex;gap:8px;flex-wrap:wrap;}
.btn-sm{padding:8px 14px !important;font-size:.85rem !important;}
.table-shell{max-height:520px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:12px;background:white;}
.table-shell table{width:100%;border-collapse:collapse;}
.table-shell thead{position:sticky;top:0;background:white;z-index:1;}
.table-shell thead th{background:#f8fafc;border-bottom:2px solid #e2e8f0;padding:12px 16px;text-align:left;font-weight:600;font-size:0.9rem;color:#374151;}
.table-shell tbody td{padding:12px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.table-shell tbody tr:hover{background:#f8fafc;}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(148px,1fr));gap:14px;margin-bottom:20px;}
.stat-card{background:white;border-radius:18px;padding:10px 12px;box-shadow:0 8px 22px rgba(16,42,67,.07);border:1px solid rgba(16,42,67,.06);text-align:center;}
.stat-val{font-size:1.90rem;font-weight:900;color:var(--teal);}
.stat-lbl{color:#627d98;font-size:0.82rem;margin-top:4px;}
.panel{background:white;border-radius:20px;padding:20px;box-shadow:0 8px 26px rgba(16,42,67,.07);border:1px solid rgba(16,42,67,.07);margin-bottom:14px;}
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;}
.filter-bar input,.filter-bar select{border-radius:11px;border:1px solid #cbd5e1;padding:9px 13px;font:inherit;flex:1;min-width:160px;font-size:0.88rem;background:white;}
.filter-bar input:focus,.filter-bar select:focus{outline:none;border-color:var(--teal);}
.section-card{background:white;border-radius:20px;padding:24px;box-shadow:0 8px 26px rgba(16,42,67,.07);border:1px solid rgba(16,42,67,.07);margin-bottom:20px;}
.section-title{font-size:1.1rem;font-weight:800;color:#102a43;}
.overlay{display:none;position:fixed;inset:0;background:rgba(16,42,67,.5);z-index:9999;align-items:center;justify-content:center;}
.overlay.show{display:flex;}
.modal-box{background:#fff;border-radius:22px;padding:32px 28px;max-width:420px;width:90%;text-align:center;}
.modal-box h3{font-size:1.15rem;margin-bottom:10px;}
.modal-box p{color:#64748b;margin-bottom:22px;}
.modal-actions{display:flex;gap:12px;justify-content:center;}

</style>
</head>
<body>
<div class="layout">

<aside class="sidebar">
    <div class="brand">
        <img class="brand-badge" src="../assets/images/logo_barchathon.jpg" alt="BarchaThon">
        <div>
            <strong>BarchaThon</strong><br>
            <small>Admin &mdash; <?php echo htmlspecialchars($user['nom']); ?></small>
        </div>
    </div>
    <nav class="side-nav">
        <a class="side-link" href="dashboard.php?tab=home">Dashboard</a>
        <a class="side-link" href="dashboard.php?tab=utilisateurs">Utilisateurs</a>
        <a class="side-link active" href="dashboard.php?tab=marathons">Marathons</a>
        <a class="side-link" href="dashboard.php?tab=stands">Stands</a>
        <a class="side-link" href="dashboard.php?tab=produits">Produits</a>
        <a class="side-link" href="dashboard.php?tab=commandes">Commandes</a>
        <a class="side-link" href="dashboard.php?tab=sponsors">Sponsors</a>
        <a class="side-link" href="../FrontOffice/accueil.php">Retour</a>
        <a class="side-link" href="../FrontOffice/logout.php">Deconnexion</a>
    </nav>
    <div class="side-note">Administration BarchaThon &mdash; details du marathon et parcours.</div>
</aside>

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
        <form method="GET" action="voirdetailsparcours.php" id="fmP" class="filter-bar">
            <input type="hidden" name="id" value="<?php echo $id_marathon; ?>">
            <input type="text" name="searchP" id="sP" placeholder="🔍 Rechercher par nom de parcours..." value="<?php echo htmlspecialchars($searchP); ?>">
            <select name="difficulte" id="dP">
                <option value="">Toutes les difficultés</option>
                <option value="facile"    <?php echo $filterDiff==='facile'?'selected':''; ?>>🟢 Facile</option>
                <option value="moyen"     <?php echo $filterDiff==='moyen'?'selected':''; ?>>🟡 Moyen</option>
                <option value="difficile" <?php echo $filterDiff==='difficile'?'selected':''; ?>>🔴 Difficile</option>
            </select>
        </form>
    </div>

<section class="section-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 class="section-title" style="margin:0;">Liste des Parcours</h2>
        <span class="tag"><?php echo count($parcoursAffich); ?> resultat(s)</span>
    </div>
    <div class="table-shell">
        <table>
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
                    <td><strong>#<?php echo (int)$p2['id_parcours']; ?></strong></td>
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

var tP;
var fmP = document.getElementById('fmP');
if (fmP) {
    document.getElementById('sP').addEventListener('input', function() {
        clearTimeout(tP); tP = setTimeout(function(){ fmP.submit(); }, 500);
    });
    document.getElementById('dP').addEventListener('change', function() { fmP.submit(); });
}
</script>
<script src="../assets/js/app.js"></script>
</body>
</html>
