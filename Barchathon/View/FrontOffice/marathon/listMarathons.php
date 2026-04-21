<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/MarathonController.php';
$controller = new MarathonController();
$search = $_GET['search'] ?? '';
$filterRegion = $_GET['region'] ?? '';
if (isset($_GET['delete'])) {
    $controller->supprimerMarathon((int)$_GET['delete']);
    header('Location: listMarathons.php');
    exit;
}

$marathons = $controller->afficherMarathon();

$stats = $controller->statsNbMarathonsDispo();
$regions = $controller->getRegions();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Back Office — Marathons | BarchaThon</title>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --card:#fff; --muted:#627d98; --coral:#e76f51; --nav:#0b2032; }
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
        .stats-grid { display:grid; gap:14px; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); margin-bottom:22px; }
        .stat-card { background:var(--card); border-radius:18px; padding:18px; box-shadow:0 8px 22px rgba(16,42,67,.07); border:1px solid rgba(16,42,67,.06); text-align:center; }
        .stat-val { font-size:1.9rem; font-weight:900; color:var(--teal); }
        .stat-lbl { color:var(--muted); font-size:0.83rem; margin-top:3px; }
        .section-card { background:var(--card); border-radius:22px; padding:20px; box-shadow:0 10px 28px rgba(16,42,67,.07); border:1px solid rgba(16,42,67,.07); margin-bottom:18px; }
        .section-title { margin:0 0 14px; font-size:1.3rem; font-weight:800; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; }
        /* FILTRE AUTO */
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .filter-bar input, .filter-bar select { border-radius:11px; border:1px solid #cbd5e1; padding:9px 13px; font:inherit; flex:1; min-width:160px; }
        .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color:var(--teal); }
        .table-shell { overflow:auto; }
        table { width:100%; min-width:860px; border-collapse:collapse; }
        th, td { padding:12px 10px; text-align:left; border-bottom:1px solid #e6edf3; vertical-align:middle; }
        th { background:#102a43; color:#fff; font-size:0.88rem; }
        tr:hover td { background:#f8fafc; }
        .table-actions { display:flex; gap:7px; flex-wrap:wrap; }
        .tag { display:inline-block; padding:4px 10px; border-radius:999px; background:rgba(15,118,110,.1); color:var(--teal); font-weight:700; font-size:.8rem; }
        .tag-coral { background:rgba(231,111,81,.1); color:var(--coral); }
        .marathon-img { width:54px; height:38px; object-fit:cover; border-radius:7px; }
        @media(max-width:960px){ .layout{grid-template-columns:1fr;} .sidebar{display:none;} }
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
            <a class="side-link active" href="listMarathons.php">🏃 Marathons</a>
            <a class="side-link" href="addMarathon.php">➕ Ajouter Marathon</a>
            <a class="side-link" href="../parcours/listParcours.php">🗺️ Parcours</a>
            <a class="side-link" href="../parcours/addParcours.php">➕ Ajouter Parcours</a>
            <a class="side-link" href="../../FrontOffice/accueil.php">↩ Retour</a>
        </nav>
    </aside>

    <main class="content">
        <div class="head">
            <div>
                <h1>🏃 Gestion des Marathons</h1>
                <div class="muted">Ajouter, modifier, supprimer et rechercher les marathons.</div>
            </div>
            <a class="btn btn-pdf" href="#">📄 Exporter PDF</a>
        </div>

        <!-- Stats : seulement Marathons affichés et Places disponibles -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-val"><?php echo count($marathons); ?></div>
                <div class="stat-lbl">Marathons affichés</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?php echo number_format((float)($stats['total_places'] ?? 0)); ?></div>
                <div class="stat-lbl">Places disponibles</div>
            </div>
        </div>

        <!-- Filtre AUTO -->
        <div class="section-card">
            <form method="GET" id="filterForm" class="filter-bar">
                <input type="text" name="search" id="searchInput" placeholder="🔍 Rechercher par nom..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="region" id="regionSelect">
                    <option value="">Toutes les régions</option>
                    <?php foreach ($regions as $r): ?>
                        <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $filterRegion===$r?'selected':''; ?>><?php echo htmlspecialchars($r); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Tableau -->
        <div class="section-card">
            <div class="section-title">
                <span>Liste des Marathons</span>
                <span class="tag"><?php echo count($marathons); ?> résultats</span>
            </div>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Nom du Marathon</th>
                            <th>Organisateur</th>
                            <th>Région</th>
                            <th>Date</th>
                            <th>Places dispo</th>
                            <th>Prix (TND)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($marathons)): ?>
                            <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:28px;">Aucun marathon trouvé.</td></tr>
                        <?php else: ?>
                            <?php foreach ($marathons as $m): ?>
                            <tr>
                                <td><strong>#<?php echo $m['id_marathon']; ?></strong></td>
                                <td><img class="marathon-img" src="../../FrontOffice/<?php echo htmlspecialchars($m['image_marathon']); ?>" onerror="this.src='../../FrontOffice/images/img1.svg'" alt=""></td>
                                <td><strong><?php echo htmlspecialchars($m['nom_marathon']); ?></strong></td>
                                <td><?php echo htmlspecialchars($m['organisateur_marathon']); ?></td>
                                <td><span class="tag">📍 <?php echo htmlspecialchars($m['region_marathon']); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($m['date_marathon'])); ?></td>
                                <td><?php echo $m['nb_places_dispo']>0 ? '<span class="tag">✅ '.$m['nb_places_dispo'].'</span>' : '<span class="tag tag-coral">❌ Complet</span>'; ?></td>
                                <td><strong><?php echo number_format($m['prix_marathon'],2); ?></strong></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-warning" href="updateMarathon.php?id=<?php echo $m['id_marathon']; ?>">✏️ Modifier</a>
                                        <a class="btn btn-danger" href="?delete=<?php echo $m['id_marathon']; ?>" onclick="return confirm('Supprimer ce marathon ?')">🗑️ Supprimer</a>
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
document.getElementById('regionSelect').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
let t;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(t);
    t = setTimeout(function(){ document.getElementById('filterForm').submit(); }, 500);
});
</script>
</body>
</html>
