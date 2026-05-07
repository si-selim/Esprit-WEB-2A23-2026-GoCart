<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/InscriptionMarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../Controller/StandController.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ProduitController.php';

$pCtrl = new ParcoursController();
$sCtrl = new StandController();
$mCtrl = new MarathonController();
$inscCtrl = new InscriptionMarathonController();
$prodCtrl = new ProduitController();

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

        .p-card { background:white; border-radius:18px; border:1px solid rgba(16,42,67,.07); box-shadow:0 6px 20px rgba(16,42,67,.07); overflow:hidden; transition:transform .2s; }
        .p-card:hover { transform:translateY(-3px); }
        .diff-band { padding:9px 16px; font-weight:800; font-size:0.83rem; letter-spacing:.04em; }
        .diff-stand { background:linear-gradient(90deg,#e0f2fe,#bae6fd); color:#0369a1; }
        .p-body { padding:16px; }
        .p-body h3 { font-size:1rem; margin-bottom:10px; }
        .p-route { display:grid; gap:6px; font-size:0.87rem; color:#486581; background:#f8fafc; border-radius:11px; padding:11px; margin-bottom:10px; }
        .dist-row { display:flex; justify-content:space-between; align-items:center; }
        .dist-val { font-size:1.35rem; font-weight:900; color:var(--teal); }

        .btn { text-decoration:none; padding:10px 15px; border-radius:12px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:7px; font-size:0.9rem; transition:opacity .15s,transform .15s,box-shadow .15s; }
        .btn:hover { opacity:.9; transform:translateY(-1px); }
        .btn-primary { background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; box-shadow:0 4px 14px rgba(22,163,74,.3); }
        .btn-primary:hover { box-shadow:0 6px 18px rgba(22,163,74,.4); }

        .parcours-filter { background:white; border-radius:14px; padding:14px 16px; margin-bottom:18px; box-shadow:0 4px 14px rgba(16,42,67,.06); display:flex; gap:10px; flex-wrap:wrap; }
        .p-search-wrap { position:relative; flex:2 1 200px; min-width:0; }
        .p-search-wrap input { width:100%; border-radius:10px; border:1px solid #cbd5e1; padding:9px 13px; font:inherit; font-size:0.9rem; }
        .p-search-wrap input:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.1); }

        .btn-mod { display:inline-flex; align-items:center; justify-content:center; gap:6px; text-decoration:none; background:linear-gradient(135deg,#cbd5e1,#e2e8f0); color:#475569; border-radius:10px; font-weight:700; font-size:0.85rem; border:0; cursor:pointer; transition:opacity .15s,transform .15s,box-shadow .15s; box-shadow:0 3px 10px rgba(203,213,225,.4); }
        .btn-mod:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 5px 14px rgba(203,213,225,.5); }
        .btn-del-card { display:inline-flex; align-items:center; justify-content:center; gap:6px; background:linear-gradient(135deg,#dc2626,#ef4444); color:#fff; border-radius:10px; font-weight:700; font-size:0.85rem; border:0; cursor:pointer; transition:opacity .15s,transform .15s,box-shadow .15s; box-shadow:0 3px 10px rgba(220,38,38,.3); }
        .btn-del-card:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 5px 14px rgba(220,38,38,.4); }

        @media(max-width:860px){ .detail-hero{grid-template-columns:1fr;} .detail-img{max-height:250px;} }

        /* Toast Notifications */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease-out;
            z-index: 10000;
            max-width: 400px;
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
<?php 
// Afficher les messages de notification
if (isset($_SESSION['success_message'])): ?>
    <script>
        window.addEventListener('load', function() {
            showToast('<?php echo addslashes($_SESSION['success_message']); ?>', 'success');
        });
    </script>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php 
if (isset($_SESSION['error_message'])): ?>
    <script>
        window.addEventListener('load', function() {
            showToast('<?php echo addslashes($_SESSION['error_message']); ?>', 'error');
        });
    </script>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

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
                    <?php if (!empty($p['heure_depart'])): ?>
                    <div class="meta-row">
                        <div class="icon">⏰</div>
                        <div><div class="label">Heure de Départ</div><div class="value"><?php echo htmlspecialchars(substr($p['heure_depart'], 0, 5)); ?></div></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($m['date_marathon'])): ?>
                    <div class="meta-row">
                        <div class="icon">🌤️</div>
                        <div><div class="label">Date du Marathon</div><div class="value"><?php echo date('d/m/Y', strtotime($m['date_marathon'])); ?></div></div>
                    </div>
                    <?php endif; ?>
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
        <span class="count" id="standsCount"><?php echo count($stands); ?></span>
    </div>

    <!-- Stands Filter -->
    <div class="parcours-filter">
        <div class="p-search-wrap" style="flex:1">
            <input type="text" id="searchStand" placeholder="🔍 Rechercher par nom de stand ou position..." autocomplete="off" onkeyup="filterStands()">
        </div>
    </div>

    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;justify-content:flex-end;">
        <a href="Stands/listStandsFront.php" class="btn" style="background:var(--teal); color:white; padding:11px 20px;"><i class="fa-solid fa-list"></i> Tous les stands</a>
        <?php if ($role === 'organisateur'): ?>
            <a href="Stands/crud-stand.php?parcours_id=<?php echo $id; ?>" class="btn btn-primary" style="padding:11px 20px;"><i class="fa-solid fa-plus"></i> Ajouter un stand</a>
        <?php endif; ?>
    </div>

    <div class="cards-grid" id="standsGrid">
        <?php if (empty($stands)): ?>
            <div class="empty-box">🏪 Aucun stand trouvé pour ce parcours.</div>
        <?php else: ?>
            <?php foreach ($stands as $s):
                $standId = $s['id_stand'] ?? $s['ID_stand'] ?? 0;
            ?>
            <div class="p-card stand-card-item">
                <div class="diff-band diff-stand">🏪 Stand</div>
                <div class="p-body">
                    <h3><?php echo htmlspecialchars($s['nom_stand'] ?? ''); ?></h3>
                    <div class="p-route">
                        <span>📍 <strong>Position :</strong> <?php echo htmlspecialchars($s['position'] ?? ''); ?></span>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($s['description'] ?? ''); ?>">📝 <strong>Description :</strong> <?php echo htmlspecialchars($s['description'] ?? 'Aucune description'); ?></span>
                    </div>
                    <div class="dist-row">
                        <div>
                            <div class="dist-val"><?php echo $prodCtrl->countProduitsByStand($standId); ?> <span style="font-size:0.85rem;color:#64748b;font-weight:700;">produit(s)</span></div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;flex-grow:1;margin-top:10px;">
                            <a href="Produits/produit.php?stand_id=<?php echo htmlspecialchars($standId); ?>&parcours_id=<?php echo $id; ?>" style="background:linear-gradient(135deg,#149184,#0eb19d);color:white;padding:6px 15px;border-radius:20px;text-decoration:none;font-weight:bold;font-size:0.85rem;display:flex;align-items:center;gap:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1);">
                                Voir détail <span style="font-size:1.1rem;">→</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php if ($role === 'organisateur'): ?>
                <div class="p-actions" style="padding:12px 16px;border-top:1px solid #e5e7eb;display:flex;gap:8px;">
                    <a href="Stands/updateStand.php?searchVal=<?php echo $standId; ?>&redirect_parcours=<?php echo $id; ?>" class="btn-mod" style="flex:1;text-align:center;padding:9px 8px;"><i class="fa-solid fa-pen-to-square"></i> Modifier</a>
                    <a href="Stands/deleteStand.php?id=<?php echo $standId; ?>&parcours_id=<?php echo $id; ?>" class="btn-del-card" style="flex:1;padding:9px 8px;" onclick="return confirm('Voulez-vous vraiment supprimer ce stand ?')"><i class="fa-solid fa-trash"></i> Supprimer</a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function filterStands() {
    var val = document.getElementById('searchStand').value.toLowerCase();
    var cards = document.querySelectorAll('.stand-card-item');
    var count = 0;
    cards.forEach(function(card) {
        var nom = card.querySelector('h3').textContent.toLowerCase();
        var routeInfo = card.querySelector('.p-route').textContent.toLowerCase();
        if (nom.indexOf(val) > -1 || routeInfo.indexOf(val) > -1) {
            card.style.display = '';
            count++;
        } else {
            card.style.display = 'none';
        }
    });
    document.getElementById('standsCount').textContent = count;
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification toast-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>
</body>
</html>
