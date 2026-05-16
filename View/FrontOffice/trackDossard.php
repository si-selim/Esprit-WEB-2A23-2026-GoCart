<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../Controller/DossardController.php";
require_once "../../Controller/InscriptionController.php";
require_once "../../Controller/ParcoursController.php";

$id_dossard = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_dossard <= 0) { header('Location: listMarathons.php'); exit; }

$dossardController     = new DossardController();
$inscriptionController = new InscriptionController();
$parcoursController    = new ParcoursController();

$dossard     = $dossardController->getById($id_dossard);
if (!$dossard) { die('Dossard introuvable.'); }

$inscription = $inscriptionController->getById($dossard['id_inscription']);
$parcours    = $parcoursController->showParcours($inscription['id_parcours'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossard #<?php echo $dossard['numero']; ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --teal:#0f766e; --ink:#102a43; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; background:linear-gradient(180deg,#fff9ef,#f2fbfb); color:var(--ink); }
        .page { width:min(480px,calc(100% - 24px)); margin:0 auto; padding:28px 0 48px; }

        .hero { background:linear-gradient(135deg,#102a43,#0f766e); border-radius:24px; padding:28px; color:white; margin-bottom:20px; display:flex; align-items:center; gap:18px; }
        .num-circle { width:72px; height:72px; border-radius:50%; border:3px solid rgba(255,255,255,.4); display:flex; align-items:center; justify-content:center; font-size:1.6rem; font-weight:900; flex-shrink:0; background:rgba(255,255,255,.15); }
        .hero-info h1 { font-size:1.35rem; font-weight:900; margin-bottom:4px; }
        .hero-info p { opacity:.8; font-size:0.88rem; }

        .card { background:white; border-radius:20px; box-shadow:0 8px 24px rgba(16,42,67,.08); padding:20px; margin-bottom:16px; border:1px solid rgba(16,42,67,.06); }
        .card h2 { font-size:1rem; font-weight:800; color:var(--ink); margin-bottom:14px; display:flex; align-items:center; gap:8px; }

        .info-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f1f5f9; font-size:0.9rem; }
        .info-row:last-child { border-bottom:none; }
        .info-label { color:#627d98; }
        .info-val { font-weight:700; }

        .badge-statut { display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:999px; font-weight:800; font-size:0.85rem; }
        .badge-paid   { background:rgba(16,185,129,.12); color:#059669; }
        .badge-unpaid { background:rgba(231,111,81,.12); color:#e76f51; }

        .couleur-dot { width:20px; height:20px; border-radius:50%; display:inline-block; border:2px solid rgba(0,0,0,.1); vertical-align:middle; margin-right:6px; }

        .cp-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; font-size:0.88rem; }
        .cp-dot { width:12px; height:12px; border-radius:50%; flex-shrink:0; }
        .cp-done   { background:#10b981; }
        .cp-active { background:#f59e0b; animation: pulse 1.5s ease-in-out infinite; }
        .cp-wait   { background:#e2e8f0; }
        .cp-line   { flex:1; height:1px; background:#e2e8f0; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        .insc-badge { background:rgba(15,118,110,.08); color:var(--teal); border-radius:10px; padding:6px 14px; font-weight:800; font-size:0.85rem; display:inline-block; margin-bottom:12px; }
    </style>
</head>
<body>
<div class="page">

    <div class="hero">
        <div class="num-circle">#<?php echo $dossard['numero']; ?></div>
        <div class="hero-info">
            <h1><?php echo htmlspecialchars($dossard['nom']); ?></h1>
            <p><?php echo htmlspecialchars($parcours['nom_parcours'] ?? 'Parcours'); ?></p>
            <p style="margin-top:4px;font-size:0.8rem;opacity:.7;">
                <?php echo number_format((float)($parcours['distance'] ?? 0), 1); ?> km —
                <?php echo htmlspecialchars($parcours['difficulte'] ?? ''); ?>
            </p>
        </div>
    </div>

    <!-- Infos dossard -->
    <div class="card">
        <h2>🎽 Dossard</h2>
        <span class="insc-badge">Inscription #<?php echo $dossard['id_inscription']; ?></span>
        <div class="info-row">
            <span class="info-label">Numéro</span>
            <span class="info-val">#<?php echo $dossard['numero']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Taille</span>
            <span class="info-val"><?php echo htmlspecialchars($dossard['taille']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Couleur</span>
            <span class="info-val">
                <span class="couleur-dot" style="background:<?php echo htmlspecialchars($dossard['couleur']); ?>"></span>
                <?php echo htmlspecialchars($dossard['couleur']); ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut paiement</span>
            <span class="badge-statut <?php echo $inscription['statut_paiement'] === 'paid' ? 'badge-paid' : 'badge-unpaid'; ?>">
                <?php echo $inscription['statut_paiement'] === 'paid' ? '✅ Payé' : '⏳ Non payé'; ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Groupe</span>
            <span class="info-val"><?php echo $inscription['nb_personnes']; ?> participant(s)</span>
        </div>
    </div>

    <!-- Parcours -->
    <?php if ($parcours): ?>
    <div class="card">
        <h2>🗺️ Parcours</h2>
        <div class="info-row">
            <span class="info-label">Départ</span>
            <span class="info-val"><?php echo htmlspecialchars($parcours['point_depart']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Arrivée</span>
            <span class="info-val"><?php echo htmlspecialchars($parcours['point_arrivee']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Distance</span>
            <span class="info-val"><?php echo number_format((float)$parcours['distance'], 2); ?> km</span>
        </div>
        <?php if (!empty($parcours['heure_depart'])): ?>
        <div class="info-row">
            <span class="info-label">Heure de départ</span>
            <span class="info-val"><?php echo substr($parcours['heure_depart'], 0, 5); ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Checkpoints simulés -->
    <div class="card">
        <h2>📍 Checkpoints</h2>
        <?php
        $dist = (float)($parcours['distance'] ?? 21);
        $cps  = [];
        $cps[] = ['label' => 'Départ', 'done' => true];
        if ($dist >= 10) $cps[] = ['label' => 'CP ' . round($dist * 0.25, 0) . ' km', 'done' => false, 'active' => true];
        if ($dist >= 20) $cps[] = ['label' => 'CP ' . round($dist * 0.5, 0) . ' km', 'done' => false];
        $cps[] = ['label' => 'Arrivée — ' . number_format($dist, 0) . ' km', 'done' => false];
        foreach ($cps as $i => $cp):
            $dotClass = $cp['done'] ? 'cp-done' : (isset($cp['active']) ? 'cp-active' : 'cp-wait');
        ?>
        <div class="cp-row">
            <div class="cp-dot <?php echo $dotClass; ?>"></div>
            <span style="<?php echo $cp['done'] ? 'color:#059669;font-weight:700;' : (isset($cp['active']) ? 'color:#d97706;font-weight:700;' : 'color:#94a3b8;'); ?>">
                <?php echo htmlspecialchars($cp['label']); ?>
            </span>
            <?php if ($i < count($cps) - 1): ?>
            <div class="cp-line"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

</div>
</body>
</html>