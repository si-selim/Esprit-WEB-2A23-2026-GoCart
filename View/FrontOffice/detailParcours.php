<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/InscriptionMarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../Controller/StandController.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';

$pCtrl = new ParcoursController();
$sCtrl = new StandController();
$mCtrl = new MarathonController();
$inscCtrl = new InscriptionMarathonController();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$currentPage = 'catalogue';
$user = getCurrentUser();
$userId = $user['id_user'] ?? $user['id'] ?? null;
if ($id <= 0) { header('Location: listMarathons.php'); exit; }
$p = $pCtrl->showParcours($id);
if (!$p) { header('Location: listMarathons.php'); exit; }

$m = $mCtrl->showMarathon($p['id_marathon']);
$stands = $sCtrl->afficherStandsParParcours($id);

$currentPage = 'catalogue';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
$isOrganizer = ($role === 'organisateur' && isset($user['id']) && $user['id'] == $m['organisateur_marathon']);

// Vérifier si l'utilisateur est déjà inscrit au marathon
$estDejaInscrit = false;
if ($role === 'participant' && $userId) {
    $estDejaInscrit = $inscCtrl->estDejaInscrit($userId, $p['id_marathon']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo htmlspecialchars($p['nom_parcours']); ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1140px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }

        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:16px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.07); font-size:0.92rem; }

        .detail-hero {
            display:grid; grid-template-columns:1fr 1fr; gap:0;
            background:white; border-radius:28px; overflow:hidden;
            box-shadow:0 16px 44px rgba(16,42,67,.1); margin-bottom:28px;
        }
        .detail-info { padding:32px; display:flex; flex-direction:column; justify-content:space-between; }
        .marathon-badge { display:inline-block; background:rgba(16,42,67,.08); color:var(--ink); border-radius:8px; padding:5px 12px; font-size:0.82rem; font-weight:700; margin-bottom:14px; }
        .detail-info h1 { font-size:1.85rem; line-height:1.2; margin-bottom:18px; }
        .meta-list { display:grid; gap:10px; margin-bottom:22px; }
        .meta-row { display:flex; align-items:center; gap:10px; font-size:0.93rem; }
        .meta-row .icon { width:32px; height:32px; border-radius:10px; background:rgba(15,118,110,.1); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
        .meta-row .label { color:#627d98; font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
        .meta-row .value { font-weight:700; color:var(--ink); }
        .detail-img { position:relative; max-height:380px; overflow:hidden; }
        .detail-img img { width:100%; height:100%; max-height:380px; object-fit:cover; display:block; }
        .img-id { position:absolute; top:16px; left:16px; background:rgba(16,42,67,.82); color:white; border-radius:9px; padding:6px 14px; font-weight:700; font-size:0.88rem; backdrop-filter:blur(6px); }

        .section-h { display:flex; align-items:center; gap:12px; margin:0 0 16px; flex-wrap:wrap; }
        .section-h h2 { font-size:1.4rem; font-weight:900; }
        .section-h .count { background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:4px 13px; font-size:0.88rem; font-weight:700; }

        .cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; margin-bottom:36px; }

        .s-card { background:white; border-radius:18px; border:1px solid rgba(16,42,67,.07); box-shadow:0 6px 20px rgba(16,42,67,.07); overflow:hidden; transition:transform .2s; }
        .s-card:hover { transform:translateY(-3px); }
        .stand-header { background:linear-gradient(135deg,#102a43,#1e3a5f); padding:14px 16px; color:white; }
        .stand-header h3 { font-size:1rem; margin-bottom:3px; }
        .stand-pos { font-size:0.82rem; opacity:.8; }
        .s-body { padding:16px; }
        .s-desc { color:#486581; font-size:0.88rem; line-height:1.6; margin-bottom:14px; }
        .btn-produits { display:inline-flex; align-items:center; gap:7px; background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; border:none; border-radius:10px; padding:9px 14px; font-weight:700; font-size:0.85rem; cursor:pointer; text-decoration:none; transition:transform .15s; }
        .btn-produits:hover { transform:translateY(-1px); }

        @media(max-width:860px){ .detail-hero{grid-template-columns:1fr;} .detail-img{max-height:250px;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="detailMarathon.php?id=<?php echo $p['id_marathon']; ?>">← Retour au marathon</a>

    <!-- DETAIL HERO -->
    <div class="detail-hero">
        <div class="detail-info">
            <div>
                <span class="marathon-badge">#<?php echo $p['id_parcours']; ?></span>
                <h1><?php echo htmlspecialchars($p['nom_parcours']); ?></h1>
                <div class="meta-list">
                    <div class="meta-row">
                        <div class="icon">🏃</div>
                        <div><div class="label">Difficulté</div><div class="value"><?php echo htmlspecialchars($p['difficulte']); ?></div></div>
                    </div>
                    <div class="meta-row">
                        <div class="icon">📏</div>
                        <div><div class="label">Distance</div><div class="value"><?php echo number_format((float)$p['distance'], 2); ?> km</div></div>
                    </div>
                    <div class="meta-row">
                        <div class="icon">📍</div>
                        <div><div class="label">Départ</div><div class="value"><?php echo htmlspecialchars($p['point_depart']); ?></div></div>
                    </div>
                    <div class="meta-row">
                        <div class="icon">🏁</div>
                        <div><div class="label">Arrivée</div><div class="value"><?php echo htmlspecialchars($p['point_arrivee']); ?></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="detail-img">
            <img src="images/hero_runner.png" alt="<?php echo htmlspecialchars($p['nom_parcours']); ?>" onerror="this.src='images/img1.svg'">
            <span class="img-id">#<?php echo $p['id_parcours']; ?></span>
        </div>
    </div>

    <!-- MARATHON PARTICIPATION SECTION -->
    <?php if ($role === 'participant'): ?>
        <div class="participation-section" style="background: linear-gradient(135deg, #102a43, #1e3a5f); color: white; border-radius: 20px; padding: 24px; margin-bottom: 28px; text-align: center;">
            <h3 style="font-size: 1.4rem; margin-bottom: 12px;">🏃‍♂️ Participation au Marathon</h3>
            <p style="opacity: 0.9; margin-bottom: 20px; font-size: 1rem;">
                <?php echo htmlspecialchars($m['nom_marathon']); ?> - <?php echo number_format($m['prix_marathon'], 2); ?> TND
            </p>
            <?php if ($estDejaInscrit): ?>
                <div style="background: rgba(16, 185, 129, 0.2); border: 2px solid #10b981; border-radius: 12px; padding: 16px; display: inline-block;">
                    <div style="font-size: 1.2rem; font-weight: bold; color: #10b981;">✅ Déjà inscrit !</div>
                    <div style="font-size: 0.9rem; opacity: 0.9;">Vous participez à ce marathon</div>
                    <a href="inscription_confirmation.php?marathon_id=<?php echo $m['id_marathon']; ?>&parcours_id=<?php echo $p['id_parcours']; ?>" style="display: inline-block; margin-top: 10px; background: #10b981; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: bold;">Voir la confirmation</a>
                </div>
            <?php elseif ($m['nb_places_dispo'] <= 0): ?>
                <div style="background: rgba(231, 111, 81, 0.2); border: 2px solid #e76f51; border-radius: 12px; padding: 16px; display: inline-block;">
                    <div style="font-size: 1.2rem; font-weight: bold; color: #e76f51;">❌ Marathon complet</div>
                    <div style="font-size: 0.9rem; opacity: 0.9;">Plus de places disponibles</div>
                </div>
            <?php else: ?>
                <a href="inscrire_marathon.php?id=<?php echo $m['id_marathon']; ?>&parcours_id=<?php echo $p['id_parcours']; ?>"
                   style="background: linear-gradient(135deg, var(--teal), #14b8a6); color: white; padding: 14px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 1rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 18px rgba(15, 118, 110, 0.3); transition: transform 0.15s;">
                    <i class="fas fa-running"></i> Participer au marathon
                </a>
                <div style="margin-top: 12px; font-size: 0.9rem; opacity: 0.8;">
                    <?php echo $m['prix_marathon'] == 0 ? 'Inscription gratuite' : 'Paiement requis'; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- STANDS SECTION -->
    <div class="section-h">
        <h2>🏪 Stands</h2>
        <span class="count"><?php echo count($stands); ?></span>
    </div>

    <?php if ($isOrganizer): ?>
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;justify-content:flex-end;">
        <a href="addStand.php?parcours_id=<?php echo $id; ?>" class="btn btn-primary" style="padding:11px 20px;"><i class="fa-solid fa-plus"></i> Ajouter un stand</a>
    </div>
    <?php endif; ?>

    <div class="cards-grid">
        <?php if (empty($stands)): ?>
            <div class="empty-box">🏪 Aucun stand trouvé pour ce parcours.</div>
        <?php else: ?>
            <?php foreach ($stands as $s):
                $standId = $s['id_stand'] ?? $s['ID_stand'] ?? 0;
            ?>
            <div class="s-card">
                <div class="stand-header">
                    <h3><?php echo htmlspecialchars($s['nom_stand'] ?? $s['nom_stand']); ?></h3>
                    <div class="stand-pos"><?php echo htmlspecialchars($s['position'] ?? ''); ?></div>
                </div>
                <div class="s-body">
                    <div class="s-desc"><?php echo htmlspecialchars($s['description'] ?? ''); ?></div>
                    <a href="produit.php?stand_id=<?php echo htmlspecialchars($standId); ?>&parcours_id=<?php echo $id; ?>" class="btn-produits">Voir les produits</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>