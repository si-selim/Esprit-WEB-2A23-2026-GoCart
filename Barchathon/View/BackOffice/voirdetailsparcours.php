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
        if ($p['difficulte'] === 'facile')         $statsFacile++;
        elseif ($p['difficulte'] === 'moyen')      $statsMoyen++;
        elseif ($p['difficulte'] === 'difficile')  $statsDifficile++;
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
        if ($p['difficulte'] === $filterDiff) {
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
.stat-card{background:#fff;border-radius:20px;padding:20px 24px;box-shadow:0 10px 28px rgba(16,42,67,.07);border:1px solid #d9e2ec;border-top:3px solid #0d9488;}
.stat-val{font-size:2.2rem;font-weight:900;color:#0d9488;line-height:1.1;}
.stat-lbl{color:#64748b;font-size:.88rem;margin-top:4px;}
.tag-easy{background:rgba(16,185,129,.1);color:#059669;}
.tag-med{background:rgba(245,158,11,.1);color:#d97706;}
.tag-hard{background:rgba(239,68,68,.1);color:#dc2626;}
.table-actions{display:flex;gap:8px;flex-wrap:wrap;}
.btn-sm{padding:8px 14px !important;font-size:.85rem !important;}
.btn-pdf{background:#0f172a;color:#fff;box-shadow:0 4px 16px rgba(15,23,42,.2);}
.marathon-info-card{background:linear-gradient(135deg,#0b2032 0%,#12314a 100%);color:#fff;border-radius:22px;padding:28px 30px;display:flex;gap:24px;align-items:center;margin-bottom:28px;box-shadow:0 14px 34px rgba(16,42,67,.15);}
.marathon-info-card img{width:90px;height:70px;object-fit:cover;border-radius:16px;flex-shrink:0;}
.mic-body h2{font-size:1.4rem;font-weight:800;margin-bottom:6px;}
.mic-sub{color:rgba(255,255,255,.7);font-size:.9rem;margin-bottom:10px;}
.mic-meta{display:flex;gap:10px;flex-wrap:wrap;}
.mic-meta span{background:rgba(255,255,255,.13);border-radius:10px;padding:5px 13px;font-size:.85rem;}
.mic-meta span small{display:block;color:rgba(255,255,255,.6);font-size:.72rem;}
.mic-meta span strong{display:block;}
.overlay{display:none;position:fixed;inset:0;background:rgba(16,42,67,.5);z-index:9999;align-items:center;justify-content:center;}
.overlay.show{display:flex;}
.modal-box{background:#fff;border-radius:22px;padding:32px 28px;max-width:420px;width:90%;text-align:center;}
.modal-box h3{font-size:1.15rem;margin-bottom:10px;}
.modal-box p{color:#64748b;margin-bottom:22px;}
.modal-actions{display:flex;gap:12px;justify-content:center;}
.table-shell table{width:100%;border-collapse:collapse;min-width:680px;}
.table-shell th{background:#102a43;color:#fff;padding:12px 14px;text-align:left;font-size:.83rem;text-transform:uppercase;letter-spacing:.04em;}
.table-shell th:first-child{border-radius:10px 0 0 0;}
.table-shell th:last-child{border-radius:0 10px 0 0;}
.table-shell td{padding:12px 14px;border-bottom:1px solid #e6edf3;vertical-align:middle;}
.table-shell tr:hover td{background:#f8fbfd;}
.section-note{color:#64748b;font-size:.85rem;margin-top:14px;}
/* PANEL */
        .panel {
            background:white; border-radius:20px; padding:20px;
            box-shadow:0 8px 26px rgba(16,42,67,.07);
            border:1px solid rgba(16,42,67,.07);
            margin-bottom:14px;
        }
        .panel-header {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:16px; flex-wrap:wrap; gap:10px;
        }
        .panel-header h2 { font-size:1.2rem; font-weight:800; }

        /* FILTRE */
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; }
        .filter-bar input,
        .filter-bar select {
            border-radius:11px; border:1px solid #cbd5e1;
            padding:9px 13px; font:inherit;
            flex:1; min-width:160px; font-size:0.88rem; background:white;
        }
        .filter-bar input:focus,
        .filter-bar select:focus { outline:none; border-color:var(--teal); }
    .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.stat-card {
    background: white;
    padding: 15px;
    border-radius: 12px;
    text-align: center;
    border-top: 4px solid #ebdfdf00;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-val {
    font-size: 24px;
    font-weight: bold;
}

.stat-lbl {
    font-size: 14px;
    color: #666;
}

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
        <h1>Gestion des parcours de ce marathon</h1>
        <div class="muted">La liste des parcours de ce marathon &mdash; consultation et suppression uniquement.</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <a class="btn btn-pdf btn-sm"
           href="../FrontOffice/parcours/exportParcoursPDF.php?marathon_id=<?php echo $id_marathon; ?>&difficulte=<?php echo urlencode($filterDiff); ?>">
            Exporter PDF
        </a>
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
        <h2 class="section-title" style="margin:0;">Parcours du Marathon</h2>
        <span class="tag"><?php echo count($parcoursAffich); ?> resultat(s)</span>
    </div>
    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du Parcours</th>
                    <th>Nom du Marathon</th>
                    <th>Point Depart</th>
                    <th>Point Arrivee</th>
                    <th>Distance (km)</th>
                    <th>Difficulte</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($parcoursAffich)): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:#64748b;">Aucun parcours trouve pour ce marathon.</td></tr>
            <?php else: ?>
                <?php foreach ($parcoursAffich as $p2): ?>
                <?php
                    $diff = $p2['difficulte'];
                    $dc = '';
                    if ($diff === 'facile')        $dc = 'tag-easy';
                    elseif ($diff === 'moyen')     $dc = 'tag-med';
                    elseif ($diff === 'difficile') $dc = 'tag-hard';
                ?>
                <tr>
                    <td><strong>#<?php echo (int)$p2['id_parcours']; ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($p2['nom_parcours']); ?></strong></td>
                    <td><?php echo htmlspecialchars($p2['nom_marathon']); ?></td>
                    <td><?php echo htmlspecialchars($p2['point_depart']); ?></td>
                    <td><?php echo htmlspecialchars($p2['point_arrivee']); ?></td>
                    <td><strong><?php echo number_format((float)$p2['distance'], 2); ?> km</strong></td>
                    <td><span class="tag <?php echo $dc; ?>"><?php echo htmlspecialchars($diff); ?></span></td>
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
