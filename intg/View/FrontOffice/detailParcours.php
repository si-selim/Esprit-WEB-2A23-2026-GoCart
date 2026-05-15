<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/lang.php';
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
    // Vérification sur la table inscription_marathon
    $estDejaInscrit = $inscCtrl->estDejaInscrit($userId, $p['id_marathon']);
    
    // Vérification sur la table inscription (via id_parcours)
    require_once __DIR__ . '/../../Controller/InscriptionController.php';
    $inscriptionCtrlObj = new InscriptionController();
    $sqlCheck = "SELECT COUNT(*) FROM inscription WHERE id_user = :user AND id_parcours = :parcours";
    $stmtCheck = Config::getConnexion()->prepare($sqlCheck);
    $stmtCheck->execute(['user' => $userId, 'parcours' => $p['id_parcours']]);
    if ($stmtCheck->fetchColumn() > 0) {
        $estDejaInscrit = true;
    }
}

// Vérifier si le marathon est terminé (date_marathon < aujourd'hui)
$marathonTermine = (!empty($m['date_marathon']) && strtotime($m['date_marathon']) < strtotime('today'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo htmlspecialchars($p['nom_parcours']); ?> — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
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

        /* COURSE MODE CSS */
        .course-mode-section { background:white; border-radius:24px; padding:28px; margin-bottom:28px; box-shadow:0 16px 44px rgba(16,42,67,.08); text-align:center; }
        .course-mode-section h2 { font-size:1.6rem; margin-bottom:12px; color:var(--ink); }
        .strava-tracking-panel { background:#ffffff; border-radius:20px; padding:12px 16px; margin-bottom:16px; box-shadow:0 8px 24px rgba(0,0,0,0.06); border:1px solid #e2e8f0; text-align:center; }
        .tracking-stats { display:flex; align-items:center; justify-content:space-between; padding:0 5px; }
        .stat-item { flex:1; display:flex; flex-direction:column; align-items:center; }
        .stat-item.left-stat { align-items:flex-start; }
        .stat-item.right-stat { align-items:flex-end; }
        .stat-val { font-size:1.8rem; font-weight:900; color:#0f172a; font-variant-numeric:tabular-nums; line-height:1.1; letter-spacing: -0.5px; }
        .stat-val.distance { color:#0f172a; }
        .stat-lbl { font-size:0.75rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-top:4px; }
        .tracking-action { flex:0 0 auto; display:flex; justify-content:center; align-items:center; margin:0 15px; position:relative; width:64px; height:64px; }
        
        .btn-strava-play { width:64px; height:64px; border-radius:50%; background:#10b981; color:white; font-size:1.6rem; border:none; cursor:pointer; box-shadow:0 6px 16px rgba(16,185,129,0.3); display:flex; align-items:center; justify-content:center; transition:transform 0.2s, box-shadow 0.2s; position:absolute; padding-left: 5px; }
        .btn-strava-play:disabled { background:#94a3b8; box-shadow:none; cursor:not-allowed; transform:none !important; }
        .btn-strava-play:hover:not(:disabled) { transform:scale(1.05); }
        
        .btn-strava-stop { width:64px; height:64px; border-radius:50%; background:#ef4444; color:white; font-size:1.6rem; border:none; cursor:pointer; box-shadow:0 6px 16px rgba(239,68,68,0.3); display:flex; align-items:center; justify-content:center; transition:transform 0.2s, box-shadow 0.2s; position:absolute; }
        .btn-strava-stop:hover { transform:scale(1.05); }
        
        .tracking-msg { margin-top:20px; font-weight:800; color:#64748b; font-size:1.05rem; }

        /* STORY MODAL CSS */
        .story-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.8); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(10px); }
        .story-modal.open { display:flex; animation: fadeIn .3s ease-out; }
        .story-content { background:white; border-radius:24px; width:100%; max-width:500px; overflow:hidden; display:flex; flex-direction:column; }
        .story-header { padding:20px; text-align:center; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
        .story-header h3 { font-size:1.4rem; font-weight:900; color:#0f172a; }
        .story-body { padding:24px; display:flex; flex-direction:column; align-items:center; gap:20px; }
        .story-preview { width:100%; max-width:360px; aspect-ratio:9/16; background:#e2e8f0; border-radius:16px; overflow:hidden; position:relative; box-shadow:0 8px 24px rgba(0,0,0,.15); }
        .story-preview img { width:100%; height:100%; object-fit:cover; display:none; }
        .story-actions { display:flex; gap:12px; width:100%; flex-wrap:wrap; justify-content:center; }
        .btn-action { flex:1; min-width:140px; padding:10px 16px; border-radius:12px; font-weight:800; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:transform .2s; }
        .btn-action:hover { transform:translateY(-2px); }
        .btn-upload { background:#10b981; color:white; }
        .btn-download { background:#10b981; color:white; }
        .btn-share { background:#8b5cf6; color:white; }
        
        #storyCanvas { display:none; }
        
        @keyframes slideDown { from{opacity:0;transform:translateY(-20px);} to{opacity:1;transform:translateY(0);} }
        @keyframes fadeIn { from{opacity:0;} to{opacity:1;} }
        
        /* CUSTOM CONFIRM MODAL */
        .custom-confirm-modal { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); z-index:10001; }
        .custom-confirm-modal.open { display:flex; animation: fadeIn .2s ease-out; }
        .custom-confirm-content { background:white; border-radius:16px; width:100%; min-width:320px; max-width:400px; box-shadow:0 12px 40px rgba(0,0,0,.2); overflow:hidden; border: 1px solid #e2e8f0; animation: slideDown .3s ease-out; }
        .custom-confirm-body { padding:24px 24px 16px; display:flex; flex-direction:column; gap:12px; }
        .custom-confirm-icon { width:48px; height:48px; border-radius:50%; background:rgba(239,68,68,.1); color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:1.4rem; margin-bottom:8px; }
        .custom-confirm-title { font-size:1.25rem; font-weight:800; color:var(--ink); }
        .custom-confirm-text { font-size:0.95rem; color:#475569; line-height:1.5; }
        .custom-confirm-actions { padding:16px 24px 24px; display:flex; gap:12px; justify-content:flex-end; }
        .btn-confirm { padding:10px 20px; border-radius:10px; font-weight:700; font-size:0.95rem; cursor:pointer; border:none; transition:all .2s; }
        .btn-confirm-cancel { background:#f1f5f9; color:#475569; }
        .btn-confirm-cancel:hover { background:#e2e8f0; color:#1e293b; }
        .btn-confirm-ok { background:linear-gradient(135deg, #ef4444, #dc2626); color:white; box-shadow:0 4px 12px rgba(239,68,68,.3); }
        .btn-confirm-ok:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(239,68,68,.4); }
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
            <img src="<?php echo (!empty($m['image_marathon']) ? htmlspecialchars($m['image_marathon']) : 'images/hero_runner.png'); ?>" alt="<?php echo htmlspecialchars($p['nom_parcours']); ?>" onerror="this.src='images/hero_runner.png'">
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
            <?php if ($marathonTermine): ?>
                <div style="background: rgba(107,114,128,.25); border: 2px solid #9ca3af; border-radius: 12px; padding: 16px; display: inline-block;">
                    <div style="font-size: 1.2rem; font-weight: bold; color: #d1d5db;">🏁 Marathon terminé</div>
                    <div style="font-size: 0.9rem; opacity: 0.8; margin-top: 6px;">Ce marathon s'est déjà déroulé le <?php echo date('d/m/Y', strtotime($m['date_marathon'])); ?></div>
                    <button disabled style="margin-top: 14px; background: #6b7280; color: #d1d5db; padding: 14px 28px; border-radius: 12px; border: none; font-weight: 700; font-size: 1rem; cursor: not-allowed; opacity: 0.6; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-ban"></i> Participer au marathon
                    </button>
                </div>
            <?php elseif ($estDejaInscrit): ?>
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
                <a href="inscription.php?id=<?php echo $m['id_marathon']; ?>&parcours_id=<?php echo $p['id_parcours']; ?>"
   style="background: linear-gradient(135deg, var(--teal), #14b8a6); color: white; padding: 14px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 1rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 18px rgba(15, 118, 110, 0.3); transition: transform 0.15s;">
    <i class="fas fa-running"></i> Participer au marathon
</a>
                <div style="margin-top: 12px; font-size: 0.9rem; opacity: 0.8;">
                    <?php echo $m['prix_marathon'] == 0 ? 'Inscription gratuite' : 'Paiement requis'; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- COURSE MODE SECTION (Visible for enrolled participants only) -->
    <?php if ($role === 'participant' && $estDejaInscrit): ?>
    <div id="courseModeSection" class="course-mode-section">
        <!-- Map and Navigation Container -->
        <div id="navMapContainer" style="margin-bottom: 24px;">
            <div id="courseMap" style="height: 500px; border-radius: 16px; overflow: hidden; border: 2px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0,0,0,0.06); margin-bottom: 16px;"></div>
            
            <div class="strava-tracking-panel">
                <div class="tracking-stats">
                    <div class="stat-item left-stat">
                        <div class="stat-val" id="statTimer">00:00:00</div>
                        <div class="stat-lbl">Temps</div>
                    </div>
                    
                    <div class="tracking-action">
                        <button id="btnStartCourse" class="btn-strava-play" disabled>
                            <i class="fas fa-play"></i>
                        </button>
                        <button id="btnStopCourse" class="btn-strava-stop" style="display:none;">
                            <i class="fas fa-square"></i>
                        </button>
                    </div>
                    
                    <div class="stat-item right-stat">
                        <div class="stat-val distance" id="statDist">0.00</div>
                        <div class="stat-lbl">Km</div>
                    </div>
                </div>
                <div id="courseStartMsg" class="tracking-msg" style="margin-top: 10px;">Recherche de position GPS...</div>
            </div>

            <div id="navInstruction" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.15), inset 0 1px 1px rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.05);">
                <!-- Current Step Header -->
                <div style="padding: 10px 14px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle at center, rgba(56,189,248,0.1) 0%, transparent 50%); pointer-events: none;"></div>
                    <div id="navIcon" style="width: 36px; height: 36px; border-radius: 10px; background: rgba(56,189,248,0.1); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: inset 0 0 0 1px rgba(56,189,248,0.2); z-index: 1;"><i class="fas fa-route"></i></div>
                    <div style="flex:1; z-index: 1;">
                        <div id="navText" style="font-size: 1rem; font-weight: 800; letter-spacing: 0.5px;">Calcul du tracé...</div>
                        <div id="navDistText" style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; margin-top: 0px; text-transform: uppercase; letter-spacing: 1px;">Veuillez patienter</div>
                    </div>
                    <button id="btnToggleVoice" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.1); color:#10b981; font-size:0.9rem; cursor:pointer; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:all 0.3s; z-index: 1; box-shadow: 0 4px 8px rgba(0,0,0,0.2);" title="Désactiver la voix">
                        <i class="fas fa-volume-up"></i>
                    </button>
                </div>
                <!-- Scrollable Steps List -->
                <div id="stepsList" style="max-height: 130px; overflow-y: auto; background: #f8fafc; color: #1e293b; display: none; flex-direction: column;">
                    <!-- Steps will be injected here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Custom Confirm Modal -->
    <div id="customConfirmModal" class="custom-confirm-modal">
        <div class="custom-confirm-content">
            <div class="custom-confirm-body">
                <div class="custom-confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="custom-confirm-title">Terminer la course ?</div>
                <div class="custom-confirm-text">Êtes-vous sûr de vouloir arrêter le chronomètre et enregistrer votre performance ? Cette action est irréversible.</div>
            </div>
            <div class="custom-confirm-actions">
                <button id="btnConfirmCancel" class="btn-confirm btn-confirm-cancel">Annuler</button>
                <button id="btnConfirmOk" class="btn-confirm btn-confirm-ok">Oui, terminer</button>
            </div>
        </div>
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

<?php if ($role === 'participant' && $estDejaInscrit): ?>
<!-- STORY MODAL -->
<div id="storyModal" class="story-modal">
    <div class="story-content">
        <div class="story-header" style="position: relative;">
            <button onclick="closeStoryModal()" style="position: absolute; top: 16px; right: 20px; background: none; border: none; font-size: 1.5rem; color: #ef4444; cursor: pointer; transition: transform 0.2s;"><i class="fas fa-times"></i></button>
            <button id="btnDownloadStory" style="display:none; position: absolute; top: 18px; right: 55px; background: #3b82f6; border: none; color: white; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-size: 1rem;"><i class="fas fa-download"></i></button>
            <h3>📸 Story Sportive</h3>
            <p style="color:#64748b; font-size:0.9rem; margin-top: 5px;">Partagez votre exploit avec le monde !</p>
        </div>
        <div class="story-body">
            <div class="story-preview" id="storyPreview">
                <img id="finalStoryImg" src="" alt="Story Preview">
                <video id="webcamVideo" autoplay playsinline style="display:none; width:100%; height:100%; object-fit:cover; position:absolute; inset:0; z-index:10;"></video>
                <div id="storyPlaceholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#94a3b8;flex-direction:column;gap:10px;">
                    <i class="fas fa-image" style="font-size:3rem;"></i>
                    <span>Ajoutez une photo</span>
                </div>
            </div>
            <input type="file" id="photoInput" accept="image/*" capture="environment" style="display:none;">
            <div class="story-actions">
                <button class="btn-action btn-upload" id="btnUploadPhoto">
                    <i class="fas fa-camera"></i> <span id="btnPhotoText">Photo</span>
                </button>

                <button class="btn-action btn-share" id="btnShareStory" style="display:none;">
                    <i class="fas fa-share-nodes"></i> Partager
                </button>
            </div>

        </div>
    </div>
</div>
<canvas id="storyCanvas" width="1080" height="1920"></canvas>

<script>
(function() {
    // === COURSE MODE LOGIC ===
    const btnStart = document.getElementById('btnStartCourse');
    const btnStop = document.getElementById('btnStopCourse');
    const startMsg = document.getElementById('courseStartMsg');
    
    const uiTimer = document.getElementById('statTimer');
    const uiDist = document.getElementById('statDist');
    const uiSpeed = document.getElementById('statSpeed');
    const uiSteps = document.getElementById('statSteps');

    // Marathon & Parcours data
    const marathonDate = "<?php echo $m['date_marathon'] ?? ''; ?>";
    const heureDepart = "<?php echo $p['heure_depart'] ?? ''; ?>";
    const marathonName = "<?php echo addslashes($m['nom_marathon']); ?>";
    const parcoursName = "<?php echo addslashes($p['nom_parcours']); ?>";
    const parcoursId = <?php echo $id; ?>;
    const pointDepartNom = "<?php echo addslashes($p['point_depart'] ?? ''); ?>";
    const pointArriveeNom = "<?php echo addslashes($p['point_arrivee'] ?? ''); ?>";
    
    let isCourseActive = false;
    let isTimeOpen = false;
    let isNearStart = null;
    
    let startTime = null;
    let timerInterval = null;
    let watchId = null;
    
    let totalDistKm = 0;
    let totalSteps = 0;
    let lastPos = null;

    let geoDepartGlobal = null;

    // Map & Navigation Variables
    let courseMap = null;
    let userMarker = null;
    let routeCoords = [];
    let navSteps = [];
    let currentStepIdx = 0;
    let voiceEnabled = true;
    const navText = document.getElementById('navText');
    const navDistText = document.getElementById('navDistText');
    const navIcon = document.getElementById('navIcon');

    let currentDistToStart = 999;

    function updateStartButton() {
        if (window.timeStatus === 'late') {
            btnStart.disabled = true;
            startMsg.textContent = "Trop tard ! Le départ est fermé (Fermeture 15 min après l'heure officielle).";
            startMsg.style.color = "#ef4444";
            return;
        }
        
        if (isNearStart === false) {
            btnStart.disabled = true;
            startMsg.textContent = "Rendez-vous au point de départ pour pouvoir commencer la course.";
            startMsg.style.color = "#e76f51";
            return;
        }
        
        if (window.timeStatus === 'early') {
            btnStart.disabled = true;
            startMsg.textContent = `Disponible dans ${window.diffMinsEarly} min (Ouverture 15 min avant le départ).`;
            startMsg.style.color = "#e76f51";
            return;
        }
        
        if (isNearStart === null) {
            btnStart.disabled = true;
            startMsg.textContent = "Recherche de votre position GPS...";
            startMsg.style.color = "#64748b";
            return;
        }
        
        // Tout est OK
        btnStart.disabled = false;
        startMsg.textContent = ""; 
    }

    function startGPSTracking() {
        if ("geolocation" in navigator) {
            watchId = navigator.geolocation.watchPosition((pos) => {
                let lat = window.manualTestLat !== undefined ? window.manualTestLat : pos.coords.latitude;
                let lon = window.manualTestLon !== undefined ? window.manualTestLon : pos.coords.longitude;
                
                // Always update the map marker
                processNavigation(lat, lon);
                
                if (isCourseActive) {
                    if (lastPos) {
                        const dist = getDistance(lastPos.lat, lastPos.lon, lat, lon);
                        if (dist > 0.005) { // min 5 meters to count as movement
                            totalDistKm += dist;
                            totalSteps += Math.floor(dist * 1300);
                            
                            uiDist.textContent = totalDistKm.toFixed(2);
                        }
                    }
                    lastPos = {lat, lon};
                } else {
                    // Pre-race check: Are we near the start?
                    if (geoDepartGlobal) {
                        currentDistToStart = getDistance(lat, lon, geoDepartGlobal.lat, geoDepartGlobal.lng);
                        const listDiv = document.getElementById('stepsList');
                        
                        if (window.timeStatus === 'late') {
                            navDistText.textContent = "Le départ est définitivement fermé.";
                            navDistText.style.color = "#ef4444";
                            isNearStart = false;
                            
                            if (listDiv) listDiv.style.display = 'none';
                            navText.textContent = "Course indisponible.";
                        } else if (currentDistToStart > 3.0) { // Plus de 3 km : bloqué
                            navDistText.innerHTML = `Éloigné du départ (${currentDistToStart.toFixed(2)} km) <button id="btnTeleport" style="background:#3b82f6;color:white;border:none;padding:2px 8px;border-radius:6px;cursor:pointer;font-weight:bold;margin-left:10px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">TEST: Téléporter ici</button>`;
                            navDistText.style.color = "#e76f51"; // Warning color
                            isNearStart = false;
                            
                            // Attacher l'événement au bouton
                            setTimeout(() => {
                                const btnTel = document.getElementById('btnTeleport');
                                if(btnTel) {
                                    btnTel.onclick = function() {
                                        window.manualTestLat = geoDepartGlobal.lat;
                                        window.manualTestLon = geoDepartGlobal.lng;
                                        // Forcer un update immédiat de la boucle
                                        processNavigation(window.manualTestLat, window.manualTestLon);
                                    };
                                }
                            }, 50);
                            
                            // Masquer les étapes si on est trop loin
                            if (listDiv) listDiv.style.display = 'none';
                            navText.textContent = "Rejoignez le point de départ pour voir les étapes.";
                        } else if (currentDistToStart > 0.05) { // Entre 50m et 3km : autorisé mais avertissement
                            navDistText.textContent = `Vous êtes proche du départ (${Math.round(currentDistToStart*1000)}m)`;
                            navDistText.style.color = "#f59e0b"; // Yellow/Orange warning
                            isNearStart = true;
                            
                            // Réafficher les étapes si on est dans les 3km
                            if (listDiv && navSteps.length > 0) listDiv.style.display = 'flex';
                            navText.textContent = "Tracé chargé !";
                        } else {
                            navDistText.textContent = "Vous êtes au point de départ.";
                            navDistText.style.color = "#10b981"; // Ready color
                            isNearStart = true;
                            
                            if (listDiv && navSteps.length > 0) listDiv.style.display = 'flex';
                            navText.textContent = "Tracé chargé !";
                        }
                        updateStartButton();
                    }
                }
            }, (err) => {
                console.warn("GPS Erreur:", err);
            }, { enableHighAccuracy: true, maximumAge: 0 });
        }
    }

    // Haversine formula
    function getDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function formatTime(secs) {
        const h = Math.floor(secs / 3600).toString().padStart(2, '0');
        const m = Math.floor((secs % 3600) / 60).toString().padStart(2, '0');
        const s = Math.floor(secs % 60).toString().padStart(2, '0');
        return `${h}:${m}:${s}`;
    }

    function formatTimeStory(secs) {
        const h = Math.floor(secs / 3600);
        const m = Math.floor((secs % 3600) / 60);
        const s = Math.floor(secs % 60);
        if (h > 0) return `${h}h ${m}m ${s}s`;
        if (m > 0) return `${m}m ${s}s`;
        return `${s}s`;
    }

    function speak(text) {
        if (!voiceEnabled) return;
        if ('speechSynthesis' in window) {
            // Remove cancel() so instructions can queue properly without cutting each other off
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'fr-FR';
            utterance.rate = 1.0;
            window.speechSynthesis.speak(utterance);
        }
    }

    // Toggle Voice
    document.getElementById('btnToggleVoice').addEventListener('click', function() {
        voiceEnabled = !voiceEnabled;
        this.style.color = voiceEnabled ? '#10b981' : '#64748b';
        this.innerHTML = voiceEnabled ? '<i class="fas fa-volume-up"></i>' : '<i class="fas fa-volume-mute"></i>';
        this.title = voiceEnabled ? 'Désactiver la voix' : 'Activer la voix';
        if(voiceEnabled) speak("Voix activée");
    });

    // Translate OSRM maneuvers to French
    function translateManeuver(maneuver) {
        let type = maneuver.type;
        let modifier = maneuver.modifier;
        let name = maneuver.name || "";
        
        let action = "Continuez";
        if (type === 'turn') {
            if (modifier.includes('left')) action = "Tournez à gauche";
            else if (modifier.includes('right')) action = "Tournez à droite";
        } else if (type === 'arrive') {
            return "Vous êtes arrivé à la fin de cette étape !";
        } else if (type === 'depart') {
            return "Commencez la course !";
        }
        
        return name ? `${action} sur ${name}` : action;
    }

    function renderStepsList() {
        const listDiv = document.getElementById('stepsList');
        if (navSteps.length === 0) {
            listDiv.style.display = 'none';
            return;
        }
        listDiv.style.display = 'flex';
        listDiv.innerHTML = '';
        
        navSteps.forEach((step, idx) => {
            const instruction = translateManeuver(step.maneuver);
            const distStr = step.distance > 1000 ? (step.distance/1000).toFixed(1) + ' km' : Math.round(step.distance) + ' m';
            
            let iconClass = "fa-arrow-up";
            let mod = step.maneuver.modifier || "";
            if(mod.includes("left")) iconClass = "fa-arrow-left";
            if(mod.includes("right")) iconClass = "fa-arrow-right";
            if(step.maneuver.type === 'depart') iconClass = "fa-walking";
            
            const stepEl = document.createElement('div');
            stepEl.id = `step-item-${idx}`;
            stepEl.style.cssText = `padding: 10px 14px; border-bottom: 1px solid rgba(0,0,0,0.04); display: flex; align-items: center; gap: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;`;
            
            // Permet d'écouter l'étape au clic
            stepEl.onclick = () => {
                speak(instruction);
            };
            
            stepEl.innerHTML = `
                <div style="width: 30px; height: 30px; border-radius: 8px; background: rgba(56,189,248,0.1); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1rem; box-shadow: inset 0 0 0 1px rgba(56,189,248,0.2);"><i class="fas ${iconClass}"></i></div>
                <div style="flex: 1;">
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a;">${instruction}</div> 
                    <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; margin-top: 0px;">pendant <span style="color:#475569;">${distStr}</span></div>
                </div>
                <div style="color: #cbd5e1; font-size: 1rem;"><i class="fas fa-volume-up"></i></div>
            `;
            listDiv.appendChild(stepEl);
        });
        
        updateStepsHighlight();
    }
    
    function updateStepsHighlight() {
        navSteps.forEach((_, idx) => {
            const el = document.getElementById(`step-item-${idx}`);
            if (el) {
                if (idx < currentStepIdx) {
                    el.style.opacity = '0.4';
                    el.style.background = '#f8fafc';
                    el.style.transform = 'scale(0.99)';
                } else if (idx === currentStepIdx) {
                    el.style.opacity = '1';
                    el.style.background = '#ffffff';
                    el.style.transform = 'scale(1)';
                    el.style.boxShadow = '0 4px 20px rgba(0,0,0,0.06)';
                    el.style.zIndex = '10';
                    el.style.position = 'relative';
                    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    el.style.opacity = '0.85';
                    el.style.background = 'transparent';
                    el.style.transform = 'scale(1)';
                }
            }
        });
    }

    // Initialize Map and Load Route
    async function initCourseMap() {
        if (!pointDepartNom || !pointArriveeNom) {
            navText.textContent = "Points de parcours manquants.";
            return;
        }

        courseMap = L.map('courseMap', { attributionControl: false }).setView([36.8065, 10.1815], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(courseMap);

        // Geocode via Nominatim or Dictionaries
        const geoDepart = await geocodePoint(pointDepartNom);
        const geoArrivee = await geocodePoint(pointArriveeNom);
        geoDepartGlobal = geoDepart;

        if (!geoDepart || !geoArrivee) {
            navText.textContent = "Impossible de localiser le tracé.";
            navDistText.textContent = "Erreur GPS";
            return;
        }

        // Fetch OSRM route with steps
        try {
            const url = `https://router.project-osrm.org/route/v1/foot/${geoDepart.lng},${geoDepart.lat};${geoArrivee.lng},${geoArrivee.lat}?overview=full&geometries=geojson&steps=true`;
            const r = await fetch(url);
            const data = await r.json();

            if (data.routes && data.routes[0]) {
                const route = data.routes[0];
                const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                
                // Draw Route
                L.polyline(coords, { color: '#3b82f6', weight: 6, opacity: 0.8 }).addTo(courseMap);
                
                // Add D and A markers
                L.marker([geoDepart.lat, geoDepart.lng], { icon: L.divIcon({ className:'', html:'<div style="width:24px;height:24px;background:#10b981;border-radius:50%;border:2px solid #fff;color:#fff;text-align:center;font-weight:bold;">D</div>' }) }).addTo(courseMap);
                L.marker([geoArrivee.lat, geoArrivee.lng], { icon: L.divIcon({ className:'', html:'<div style="width:24px;height:24px;background:#ef4444;border-radius:50%;border:2px solid #fff;color:#fff;text-align:center;font-weight:bold;">A</div>' }) }).addTo(courseMap);
                
                courseMap.fitBounds(L.latLngBounds(coords), { padding: [30, 30] });
                
                // Correction bug tiles gris ultra-robuste avec ResizeObserver
                const resizeObserver = new ResizeObserver(() => {
                    if (courseMap) {
                        courseMap.invalidateSize();
                    }
                });
                resizeObserver.observe(document.getElementById('courseMap'));

                // Délai initial au cas où
                setTimeout(() => {
                    if (courseMap) {
                        courseMap.invalidateSize();
                        courseMap.fitBounds(L.latLngBounds(coords), { padding: [30, 30] });
                    }
                }, 800);

                // Extract Navigation Steps
                if (route.legs && route.legs[0].steps) {
                    navSteps = route.legs[0].steps.filter(s => s.distance > 5); // Filter tiny steps
                }

                navText.textContent = "Tracé chargé !";
                navDistText.textContent = "En attente du départ...";
                
                renderStepsList();
            }
        } catch(e) {
            console.error("OSRM Error", e);
        }
        
        // Start tracking user position immediately
        startGPSTracking();
    }

    async function geocodePoint(name) {
        // 1. Search in our hardcoded dictionary (REGION_POINTS) first
        const REGION_POINTS = {
          'Nabeul': [
            { lat: 36.4513, lng: 10.7357, nom: 'Nabeul Centre' }, { lat: 36.4441, lng: 10.7268, nom: 'Gare Nabeul' }, { lat: 36.4350, lng: 10.7452, nom: 'Plage Nabeul' }, { lat: 36.4280, lng: 10.7600, nom: 'Bir Rekba' }, { lat: 36.4680, lng: 10.7480, nom: 'Manzel Temim' }, { lat: 36.4760, lng: 10.7220, nom: 'Beni Khiar' }, { lat: 36.4900, lng: 10.7350, nom: 'Korba' }, { lat: 36.4150, lng: 10.7550, nom: 'Dar Chaabane' }, { lat: 36.4601, lng: 10.7310, nom: 'Marché Central Nabeul' },
          ],
          'Hammamet': [
            { lat: 36.4000, lng: 10.5560, nom: 'Hammamet Centre' }, { lat: 36.3960, lng: 10.5630, nom: 'Plage Hammamet' }, { lat: 36.4100, lng: 10.5480, nom: 'Hammamet Nord' }, { lat: 36.3850, lng: 10.5720, nom: 'Marina Hammamet' }, { lat: 36.4210, lng: 10.5350, nom: 'Hammamet Yasmine' }, { lat: 36.3750, lng: 10.5800, nom: 'Hammamet Sud' }, { lat: 36.4050, lng: 10.5200, nom: 'Bou Argoub' }, { lat: 36.4300, lng: 10.5600, nom: 'Grombalia' }, { lat: 36.3900, lng: 10.5900, nom: 'Corniche Hammamet' },
          ],
          'Tunis': [
            { lat: 36.8190, lng: 10.1658, nom: 'Avenue Habib Bourguiba' }, { lat: 36.8509, lng: 10.1944, nom: 'Sidi Bou Saïd' }, { lat: 36.8579, lng: 10.3247, nom: 'La Marsa' }, { lat: 36.8438, lng: 10.2464, nom: 'Carthage' }, { lat: 36.8300, lng: 10.1500, nom: 'Bardo' }, { lat: 36.8100, lng: 10.1800, nom: 'Les Berges du Lac' }, { lat: 36.8400, lng: 10.1400, nom: 'El Menzah' }, { lat: 36.7900, lng: 10.1700, nom: 'Montplaisir' }, { lat: 36.8000, lng: 10.2200, nom: 'La Goulette' },
          ],
          'Sousse': [
            { lat: 35.8281, lng: 10.6369, nom: 'Sousse Médina' }, { lat: 35.8400, lng: 10.6200, nom: 'Port El Kantaoui' }, { lat: 35.8100, lng: 10.6500, nom: 'Sousse Plage' }, { lat: 35.8600, lng: 10.6000, nom: 'Akouda' }, { lat: 35.7900, lng: 10.6700, nom: 'Chott Mariam' }, { lat: 35.8700, lng: 10.5900, nom: 'Hammam Sousse' }, { lat: 35.8300, lng: 10.6800, nom: 'Corniche Sousse' }, { lat: 35.8000, lng: 10.6300, nom: 'Sahloul' }, { lat: 35.8450, lng: 10.6450, nom: 'Khezama' },
          ],
          'Sfax': [
            { lat: 34.7406, lng: 10.7603, nom: 'Sfax Médina' }, { lat: 34.7300, lng: 10.7800, nom: 'Sfax Plage' }, { lat: 34.7600, lng: 10.7400, nom: 'Sfax Centre' }, { lat: 34.7800, lng: 10.7600, nom: 'Route Tunis-Sfax' }, { lat: 34.7100, lng: 10.7700, nom: 'Sfax Sud' }, { lat: 34.7500, lng: 10.8000, nom: 'Sakiet Eddaier' }, { lat: 34.7900, lng: 10.7200, nom: 'Sfax Nord' }, { lat: 34.7200, lng: 10.7500, nom: 'Sfax Corniche' }, { lat: 34.7700, lng: 10.7900, nom: 'Mahres' },
          ],
          'Monastir': [
            { lat: 35.7643, lng: 10.8113, nom: 'Monastir Centre' }, { lat: 35.7750, lng: 10.8000, nom: 'Skanes Monastir' }, { lat: 35.7500, lng: 10.8250, nom: 'Monastir Plage' }, { lat: 35.7850, lng: 10.7900, nom: 'Ksar Hellal' }, { lat: 35.7400, lng: 10.8400, nom: 'Monastir Port' }, { lat: 35.7650, lng: 10.8350, nom: 'Corniche Monastir' }, { lat: 35.7900, lng: 10.8100, nom: 'Moknine' }, { lat: 35.7300, lng: 10.8200, nom: 'Lamta' }, { lat: 35.7550, lng: 10.8450, nom: 'Teboulba' },
          ],
          'Bizerte': [
            { lat: 37.2744, lng: 9.8739, nom: 'Bizerte Centre' }, { lat: 37.2600, lng: 9.8900, nom: 'Port Bizerte' }, { lat: 37.2850, lng: 9.8600, nom: 'Lac de Bizerte' }, { lat: 37.2500, lng: 9.9100, nom: 'Zarzouna' }, { lat: 37.2950, lng: 9.8400, nom: 'Bizerte Nord' }, { lat: 37.2300, lng: 9.9300, nom: 'Menzel Bourguiba' }, { lat: 37.2700, lng: 9.8300, nom: 'Remel Plage' }, { lat: 37.3000, lng: 9.8600, nom: 'Cap Blanc' }, { lat: 37.2450, lng: 9.8700, nom: 'Ain Mariem' },
          ],
          'Kairouan': [
            { lat: 35.6781, lng: 10.0963, nom: 'Kairouan Médina' }, { lat: 35.6850, lng: 10.1100, nom: 'Grande Mosquée Kairouan' }, { lat: 35.6700, lng: 10.0800, nom: 'Kairouan Sud' }, { lat: 35.6950, lng: 10.1200, nom: 'Route Tunis-Kairouan' }, { lat: 35.6600, lng: 10.1300, nom: 'Kairouan Nord' }, { lat: 35.6450, lng: 10.0900, nom: 'El Alaa' }, { lat: 35.7100, lng: 10.0700, nom: 'Sbikha' }, { lat: 35.6800, lng: 10.0600, nom: 'Oued Zeroud' }, { lat: 35.7000, lng: 10.1400, nom: 'Route Sousse-Kairouan' },
          ],
          'Ariana': [
            { lat: 36.8923, lng: 10.1939, nom: 'Ariana Ville' }, { lat: 36.9003, lng: 10.2018, nom: 'Raoued' }, { lat: 36.8760, lng: 10.1710, nom: 'Ettadhamen' }, { lat: 36.8850, lng: 10.2150, nom: 'La Soukra' }, { lat: 36.9100, lng: 10.1600, nom: 'Kalaat El Andalous' }, { lat: 36.8700, lng: 10.2300, nom: 'Borj Louzir' }, { lat: 36.9200, lng: 10.1800, nom: 'Sidi Thabet' }, { lat: 36.8630, lng: 10.1870, nom: 'Ennasr' }, { lat: 36.9050, lng: 10.2250, nom: 'Technopole El Ghazela' },
          ],
          'Gabès': [
            { lat: 33.8881, lng: 10.0975, nom: 'Gabès Centre' }, { lat: 33.8760, lng: 10.1100, nom: 'Gabès Médina' }, { lat: 33.9000, lng: 10.0800, nom: 'Gabès Plage' }, { lat: 33.8650, lng: 10.1200, nom: 'Cité Administrative Gabès' }, { lat: 33.9150, lng: 10.0700, nom: 'Jara Gabès' }, { lat: 33.8500, lng: 10.1350, nom: 'Chenini Gabès' }, { lat: 33.9300, lng: 10.0600, nom: 'Métouia' }, { lat: 33.8400, lng: 10.1500, nom: 'El Hamma' }, { lat: 33.9400, lng: 10.0500, nom: 'Ghannouch' },
          ]
        };

        for (let region in REGION_POINTS) {
            for (let point of REGION_POINTS[region]) {
                if (point.nom.toLowerCase() === name.toLowerCase()) {
                    return { lat: point.lat, lng: point.lng };
                }
            }
        }

        // 2. Fallback to Nominatim
        try {
            const r = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(name+', Tunisie')}&format=json&limit=1&countrycodes=tn`);
            const d = await r.json();
            if (d && d[0]) return { lat: parseFloat(d[0].lat), lng: parseFloat(d[0].lon) };
        } catch(e) {
            console.warn("Nominatim error", e);
        }
        
        return null;
    }

    // Process GPS position for Navigation
    function processNavigation(lat, lon) {
        if (!courseMap) return;

        const currentPos = [lat, lon];

        // Update User Marker
        if (!userMarker) {
            userMarker = L.marker(currentPos, {
                icon: L.divIcon({ className:'', html:'<div style="width:18px;height:18px;background:#3b82f6;border-radius:50%;border:3px solid #fff;box-shadow:0 0 10px rgba(59,130,246,0.8);"></div>' })
            }).addTo(courseMap);
            courseMap.setView(currentPos, 16);
        } else {
            userMarker.setLatLng(currentPos);
            // Auto-pan if map is not being dragged
            courseMap.panTo(currentPos); 
        }

        // Navigation Logic (Turn-by-turn) - ONLY IF COURSE IS ACTIVE
        if (!isCourseActive) return;
        
        if (navSteps.length > 0 && currentStepIdx < navSteps.length) {
            const step = navSteps[currentStepIdx];
            const stepLoc = step.maneuver.location; // [lng, lat]
            const distToStepKm = getDistance(lat, lon, stepLoc[1], stepLoc[0]);
            const distToStepM = Math.round(distToStepKm * 1000);

            // If we are close to the maneuver point (< 20 meters), trigger instruction and move to next
            if (distToStepM < 20) {
                const instruction = translateManeuver(step.maneuver);
                navText.textContent = instruction;
                navDistText.textContent = "Maintenant";
                speak(instruction);
                currentStepIdx++;
                updateStepsHighlight();
            } else {
                // Just display upcoming step
                const instruction = translateManeuver(step.maneuver);
                navText.textContent = instruction;
                navDistText.textContent = `Dans ${distToStepM} mètres`;
                
                // Icon handling based on modifier
                let iconClass = "fa-arrow-up";
                let mod = step.maneuver.modifier || "";
                if(mod.includes("left")) iconClass = "fa-arrow-left";
                if(mod.includes("right")) iconClass = "fa-arrow-right";
                navIcon.innerHTML = `<i class="fas ${iconClass}"></i>`;
            }
        } else if (currentStepIdx >= navSteps.length && navSteps.length > 0) {
            navText.textContent = "Vous êtes arrivé !";
            navDistText.textContent = "";
            navIcon.innerHTML = `<i class="fas fa-flag-checkered"></i>`;
        }
    }

    // Check if course can start (20 min before start time)
    function checkStartTime() {
        if (!marathonDate || !heureDepart) {
            startMsg.textContent = "Date/heure non définies. (Mode test: activé)";
            isTimeOpen = true;
            updateStartButton();
            return;
        }
        
        const now = new Date();
        const [yyyy, mm, dd] = marathonDate.split('-');
        const [hours, mins, secs] = heureDepart.split(':');
        
        // Construct the official start date object
        const startDateTime = new Date(yyyy, mm - 1, dd, hours, mins, secs || 0);
        
        // Diff in minutes
        const diffMs = startDateTime - now;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins > 15) {
            // Trop tôt : plus de 15 minutes avant
            isTimeOpen = false;
            window.timeStatus = 'early';
            window.diffMinsEarly = diffMins - 15;
            updateStartButton();
        } else if (diffMins < -15) {
            // Trop tard : plus de 15 minutes après
            isTimeOpen = false;
            window.timeStatus = 'late';
            updateStartButton();
        } else {
            // Dans la fenêtre [-15 min, +15 min]
            isTimeOpen = true;
            window.timeStatus = 'open';
            updateStartButton();
        }
    }
    
    // Init map as soon as page loads
    setTimeout(initCourseMap, 500);

    setInterval(checkStartTime, 60000);
    checkStartTime();

    btnStart.addEventListener('click', () => {
        isCourseActive = true;
        
        btnStart.style.display = 'none';
        btnStop.style.display = 'flex';
        startMsg.textContent = "Enregistrement en cours...";
        startMsg.style.color = "#10b981";
        
        totalDistKm = 0;
        totalSteps = 0;
        lastPos = null;
        startTime = new Date();
        
        speak("Course démarrée. Bonne chance !");
        if(courseMap && userMarker) {
             courseMap.setView(userMarker.getLatLng(), 18);
        }
        
        // Timer
        timerInterval = setInterval(() => {
            const now = new Date();
            const elapsedSecs = Math.floor((now - startTime) / 1000);
            uiTimer.textContent = formatTime(elapsedSecs);
            
            // Speed = Dist / Time (in hours)
            const elapsedHours = elapsedSecs / 3600;
            const speed = elapsedHours > 0 ? (totalDistKm / elapsedHours) : 0;
            uiSpeed.textContent = speed.toFixed(1);
            
        }, 1000);
    });

    btnStop.addEventListener('click', (e) => {
        e.preventDefault(); // Empêcher le comportement par défaut (retour en haut de page)
        // Show custom confirm modal instead of browser confirm
        const confirmModal = document.getElementById('customConfirmModal');
        confirmModal.classList.add('open');
    });

    document.getElementById('btnConfirmCancel').addEventListener('click', () => {
        document.getElementById('customConfirmModal').classList.remove('open');
    });

    document.getElementById('btnConfirmOk').addEventListener('click', () => {
        document.getElementById('customConfirmModal').classList.remove('open');
        
        isCourseActive = false;
        clearInterval(timerInterval);
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        
        speak("Course terminée. Félicitations !");
        
        const finalTimeSecs = Math.floor((new Date() - startTime) / 1000);
        const elapsedHours = finalTimeSecs / 3600;
        const finalSpeed = elapsedHours > 0 ? (totalDistKm / elapsedHours) : 0;
        
        // Save to DB via AJAX
        saveCourseData(parcoursId, totalDistKm, finalTimeSecs, finalSpeed, totalSteps);
        
        // Open Story Modal with parcoursName instead of marathonName
        openStoryModal(totalDistKm, finalTimeSecs, totalSteps, parcoursName, marathonDate);
    });

    function saveCourseData(id_parcours, dist, temps, vitesse, pas) {
        fetch('save_course.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_parcours: id_parcours,
                distance: dist,
                temps: temps,
                vitesse: vitesse,
                pas: pas
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast("Performance enregistrée avec succès !", "success");
            } else {
                showToast("Erreur lors de l'enregistrement.", "error");
            }
        })
        .catch(err => console.error("Erreur save course:", err));
    }

    // === STORY LOGIC ===
    let currentStoryBlob = null;
    const storyModal = document.getElementById('storyModal');
    const photoInput = document.getElementById('photoInput');
    const finalImg = document.getElementById('finalStoryImg');
    const placeholder = document.getElementById('storyPlaceholder');
    const btnDownload = document.getElementById('btnDownloadStory');
    const btnShare = document.getElementById('btnShareStory');
    const canvas = document.getElementById('storyCanvas');
    const ctx = canvas.getContext('2d');
    
    // Webcam elements
    const webcamVideo = document.getElementById('webcamVideo');
    const btnUploadPhoto = document.getElementById('btnUploadPhoto');
    const btnPhotoText = document.getElementById('btnPhotoText');
    let webcamStream = null;
    
    let storyData = {};

    window.openStoryModal = function(dist, timeSecs, steps, mName, mDate) {
        // Formater la date en JJ/MM/AAAA
        let fDate = mDate || new Date().toISOString().split('T')[0];
        if (fDate.includes('-')) {
            const p = fDate.split('-');
            fDate = p[2] + '/' + p[1] + '/' + p[0];
        }

        storyData = {
            dist: dist.toFixed(2) + " km",
            time: formatTimeStory(timeSecs),
            steps: steps.toString(),
            mName: mName,
            mDate: fDate
        };
        storyModal.classList.add('open');
    };

    window.closeStoryModal = function() {
        if (webcamStream) {
            webcamStream.getTracks().forEach(track => track.stop());
            webcamStream = null;
        }
        storyModal.classList.remove('open');
        // Reset state (reload to refresh page optionally)
        window.location.reload();
    };

    btnUploadPhoto.addEventListener('click', async () => {
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (webcamStream) {
            // Take picture from webcam stream
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = webcamVideo.videoWidth;
            tempCanvas.height = webcamVideo.videoHeight;
            tempCanvas.getContext('2d').drawImage(webcamVideo, 0, 0);
            
            // Stop webcam
            webcamStream.getTracks().forEach(track => track.stop());
            webcamStream = null;
            webcamVideo.style.display = 'none';
            
            // Reset button
            btnUploadPhoto.style.background = '#10b981';
            btnPhotoText.textContent = "Changer Photo";
            btnUploadPhoto.querySelector('i').className = "fas fa-camera";
            
            const img = new Image();
            img.onload = () => generateStoryImage(img);
            img.src = tempCanvas.toDataURL('image/jpeg');
            return;
        }

        if (isMobile) {
            // On mobile, trigger the file input which now has capture="environment"
            photoInput.click();
        } else {
            // On PC, try to start the webcam
            try {
                webcamStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
                webcamVideo.srcObject = webcamStream;
                webcamVideo.style.display = 'block';
                placeholder.style.display = 'none';
                finalImg.style.display = 'none';
                
                // Change button to "Capture"
                btnUploadPhoto.style.background = '#ef4444'; // Red
                btnPhotoText.textContent = "Capturer";
                btnUploadPhoto.querySelector('i').className = "fas fa-dot-circle";
            } catch (err) {
                console.error("Webcam error:", err);
                // Fallback to file input if webcam fails or is denied
                photoInput.click();
            }
        }
    });

    photoInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        const img = new Image();
        const reader = new FileReader();
        
        reader.onload = (event) => {
            img.onload = () => {
                generateStoryImage(img);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });

    function generateStoryImage(bgImg) {
        // Draw background (cover)
        const scale = Math.max(canvas.width / bgImg.width, canvas.height / bgImg.height);
        const x = (canvas.width / 2) - (bgImg.width / 2) * scale;
        const y = (canvas.height / 2) - (bgImg.height / 2) * scale;
        
        ctx.fillStyle = '#1e293b';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(bgImg, x, y, bgImg.width * scale, bgImg.height * scale);
        
        // Draw Dark Gradient Overlay (Strava style - darker at top and bottom)
        const gradTop = ctx.createLinearGradient(0, 0, 0, 800);
        gradTop.addColorStop(0, 'rgba(0,0,0,0.7)');
        gradTop.addColorStop(1, 'transparent');
        ctx.fillStyle = gradTop;
        ctx.fillRect(0, 0, canvas.width, 800);

        const gradBot = ctx.createLinearGradient(0, canvas.height - 600, 0, canvas.height);
        gradBot.addColorStop(0, 'transparent');
        gradBot.addColorStop(1, 'rgba(0,0,0,0.85)');
        ctx.fillStyle = gradBot;
        ctx.fillRect(0, canvas.height - 600, canvas.width, 600);
        
        // Draw Texts
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        
        // Add subtle shadow for visibility
        ctx.shadowColor = 'rgba(0,0,0,0.8)';
        ctx.shadowBlur = 12;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 4;
        
        // Font setup (clean, modern, finer)
        const fontFamily = '"Inter", "Nunito", "Segoe UI", "Helvetica Neue", sans-serif';
        const leftMargin = 80;
        
        // Un peu en bas du milieu (milieu = 960)
        let startY = 1050;
        
        // Section 1: Distance
        ctx.fillStyle = '#f1f5f9';
        ctx.font = `400 30px ${fontFamily}`;
        ctx.fillText("Distance", leftMargin, startY);
        ctx.fillStyle = 'white';
        ctx.font = `500 80px ${fontFamily}`;
        ctx.fillText(storyData.dist, leftMargin, startY + 55);
        
        // Section 2: Steps
        startY += 150;
        ctx.fillStyle = '#f1f5f9';
        ctx.font = `400 30px ${fontFamily}`;
        ctx.fillText("Steps", leftMargin, startY);
        ctx.fillStyle = 'white';
        ctx.font = `500 80px ${fontFamily}`;
        ctx.fillText(storyData.steps, leftMargin, startY + 55);
        
        // Section 3: Time
        startY += 150;
        ctx.fillStyle = '#f1f5f9';
        ctx.font = `400 30px ${fontFamily}`;
        ctx.fillText("Time", leftMargin, startY);
        ctx.fillStyle = 'white';
        ctx.font = `500 80px ${fontFamily}`;
        ctx.fillText(storyData.time, leftMargin, startY + 55);
        
        // Bottom Logo / Branding (Below stats)
        startY += 180;
        ctx.fillStyle = 'white';
        ctx.font = `900 65px ${fontFamily}`;
        ctx.fillText("BARCHATHON", leftMargin, startY);


        // Convert to data URL
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        finalImg.src = dataUrl;
        finalImg.style.display = 'block';
        placeholder.style.display = 'none';
        
        btnDownload.style.display = 'flex';
        
        // Save Blob for sharing
        canvas.toBlob((blob) => {
            currentStoryBlob = blob;
            if (navigator.canShare && navigator.canShare({ files: [new File([blob], "story.jpg", {type: "image/jpeg"})] })) {
                btnShare.style.display = 'flex';
            }
        }, 'image/jpeg', 0.9);
    }

    btnDownload.addEventListener('click', () => {
        const a = document.createElement('a');
        a.href = finalImg.src;
        a.download = `barchathon_${Date.now()}.jpg`;
        a.click();
    });

    btnShare.addEventListener('click', async () => {
        if (!currentStoryBlob) return;
        const file = new File([currentStoryBlob], "barchathon_story.jpg", { type: "image/jpeg" });
        try {
            await navigator.share({
                title: 'Ma Course sur BarchaThon',
                text: `Je viens de terminer ${storyData.mName} avec une distance de ${storyData.dist} ! 🏃‍♂️🔥`,
                files: [file]
            });
        } catch (err) {
            console.log("Erreur partage: ", err);
        }
    });

})();
</script>
<?php endif; ?>
</body>
</html>
