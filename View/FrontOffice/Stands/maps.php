<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
require_once __DIR__ . '/../../../Controller/StandController.php';
$standCtrl = new StandController();
$stands = $standCtrl->getStandsWithCoords();
$user = getCurrentUser();
$currentPage = 'stands';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation GPS | BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        :root{--ink:#102a43;--teal:#0f766e}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:"Segoe UI",sans-serif;color:var(--ink);background:#f2fbfb}
        #map{width:100%;height:calc(100vh - 70px);z-index:1}
        .map-sidebar{position:fixed;top:80px;left:16px;z-index:1000;width:340px;background:rgba(255,255,255,.97);border-radius:20px;padding:22px 18px;box-shadow:0 16px 48px rgba(16,42,67,.14);backdrop-filter:blur(10px);max-height:calc(100vh - 100px);overflow-y:auto}
        .map-sidebar::-webkit-scrollbar{width:5px}
        .map-sidebar::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}
        .sidebar-header{display:flex;align-items:center;gap:12px;margin-bottom:6px}
        .sidebar-header-icon{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#2563eb,#3b82f6);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:white}
        .sidebar-header h2{font-size:1.15rem;font-weight:800}
        .sidebar-sub{color:#64748b;font-size:0.82rem;margin-bottom:14px;line-height:1.5}
        .legend{display:flex;gap:16px;margin-bottom:14px;flex-wrap:wrap}
        .legend-item{display:flex;align-items:center;gap:6px;font-size:0.8rem;font-weight:600}
        .legend-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0}
        .legend-dot.user{background:#2563eb;border:2px solid #1d4ed8}
        .legend-dot.stand{background:#10b981;border:2px solid #059669}
        .legend-dot.route{background:#e11d48;border:2px solid #be123c}
        .nav-panel{display:none;background:linear-gradient(135deg,#1e3a5f,#102a43);color:white;border-radius:16px;padding:16px;margin-bottom:14px}
        .nav-panel.visible{display:block}
        .nav-current{font-size:1rem;font-weight:800;margin-bottom:6px;display:flex;align-items:center;gap:8px}
        .nav-next{font-size:0.82rem;opacity:.7;margin-bottom:10px}
        .nav-metrics{display:flex;gap:12px}
        .nav-metric{background:rgba(255,255,255,.1);border-radius:10px;padding:8px 12px;text-align:center;flex:1}
        .nav-metric .val{font-size:1.1rem;font-weight:900}
        .nav-metric .lbl{font-size:0.65rem;opacity:.7;text-transform:uppercase}
        .nav-btn{width:100%;padding:10px;border:none;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s}
        .nav-btn-start{background:linear-gradient(135deg,#16a34a,#22c55e);color:white}
        .nav-btn-stop{background:linear-gradient(135deg,#dc2626,#ef4444);color:white}
        .nav-btn:hover{transform:translateY(-1px)}
        .route-info{display:none;background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #93c5fd;border-radius:14px;padding:14px;margin-bottom:14px}
        .route-info.visible{display:block}
        .route-info-title{font-size:0.82rem;font-weight:800;color:#1e40af;margin-bottom:8px}
        .route-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
        .route-metric{text-align:center}
        .route-metric .val{font-size:1.3rem;font-weight:900;color:#1e3a5f}
        .route-metric .lbl{font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase}
        .steps-list{max-height:200px;overflow-y:auto;margin-top:10px;border-top:1px solid #bfdbfe;padding-top:8px}
        .step-item{display:flex;align-items:flex-start;gap:8px;padding:6px 0;font-size:0.8rem;color:#334155;border-bottom:1px solid #f1f5f9}
        .step-item.active{color:#2563eb;font-weight:700;background:rgba(37,99,235,.05);border-radius:8px;padding:6px 8px}
        .step-icon{font-size:1rem;flex-shrink:0;width:24px;text-align:center}
        .stand-list-title{font-size:0.78rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
        .stand-list{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
        .stand-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:12px;border:1px solid #e2e8f0;background:white;cursor:pointer;transition:.2s}
        .stand-item:hover,.stand-item.active{border-color:#2563eb;background:rgba(37,99,235,.04);box-shadow:0 2px 8px rgba(37,99,235,.12)}
        .stand-item-icon{width:30px;height:30px;border-radius:8px;background:rgba(16,185,129,.1);color:#10b981;display:flex;align-items:center;justify-content:center;font-size:0.85rem;flex-shrink:0}
        .stand-item-info{flex:1;min-width:0}
        .stand-item-name{font-weight:700;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .stand-item-pos{font-size:0.72rem;color:#64748b}
        .stand-item-dist{font-size:0.72rem;font-weight:700;color:#2563eb;white-space:nowrap}
        .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--teal);text-decoration:none;font-weight:700;font-size:0.88rem;margin-top:6px}
        .route-loading{display:none;text-align:center;padding:8px;font-size:0.85rem;color:#2563eb;font-weight:600}
        .route-loading.visible{display:block}
        .route-loading i{animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        @media(max-width:768px){.map-sidebar{position:fixed;bottom:0;left:0;top:auto;width:100%;border-radius:20px 20px 0 0;max-height:45vh;padding:16px 14px}}
    </style>
</head>
<body>
<?php require __DIR__ . '/../partials/topbar.php'; ?>
<div id="map"></div>
<div class="map-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-header-icon"><i class="fas fa-map-marked-alt"></i></div>
        <h2>Navigation GPS</h2>
    </div>
    <p class="sidebar-sub">Cliquez sur un stand puis "Démarrer" pour la navigation vocale en temps réel.</p>
    <div class="legend">
        <div class="legend-item"><div class="legend-dot user"></div>Vous</div>
        <div class="legend-item"><div class="legend-dot stand"></div>Stand</div>
        <div class="legend-item"><div class="legend-dot route"></div>Trajet</div>
    </div>
    <div class="nav-panel" id="navPanel">
        <div class="nav-current"><span id="navIcon">➡️</span> <span id="navInstruction">En attente...</span></div>
        <div class="nav-next" id="navNext"></div>
        <div class="nav-metrics">
            <div class="nav-metric"><div class="val" id="navDist">--</div><div class="lbl">Restant</div></div>
            <div class="nav-metric"><div class="val" id="navTime">--</div><div class="lbl">Durée</div></div>
            <div class="nav-metric"><div class="val" id="navStep">--</div><div class="lbl">Étape</div></div>
        </div>
    </div>
    <button class="nav-btn nav-btn-start" id="btnStartNav" style="display:none" onclick="startNavigation()"><i class="fas fa-play"></i> Démarrer la navigation</button>
    <button class="nav-btn nav-btn-stop" id="btnStopNav" style="display:none" onclick="stopNavigation()"><i class="fas fa-stop"></i> Arrêter la navigation</button>
    <div class="route-info" id="routeInfo">
        <div class="route-info-title" id="routeStandName">🧭 Itinéraire</div>
        <div class="route-info-grid">
            <div class="route-metric"><div class="val" id="routeDist">--</div><div class="lbl">Distance</div></div>
            <div class="route-metric"><div class="val" id="routeTime">--</div><div class="lbl">Durée</div></div>
        </div>
        <div class="steps-list" id="stepsList"></div>
    </div>
    <div class="route-loading" id="routeLoading"><i class="fas fa-spinner"></i> Calcul...</div>
    <div class="stand-list-title">🏪 Stands (<span id="standCount"><?= count($stands) ?></span>)</div>
    <div class="stand-list" id="standList">
        <?php foreach ($stands as $s): ?>
        <div class="stand-item" data-lat="<?= $s['lat'] ?>" data-lon="<?= $s['lon'] ?>" data-name="<?= htmlspecialchars($s['nom_stand']) ?>" data-pos="<?= htmlspecialchars($s['position']) ?>" onclick="selectStand(this)">
            <div class="stand-item-icon">🏪</div>
            <div class="stand-item-info">
                <div class="stand-item-name"><?= htmlspecialchars($s['nom_stand']) ?></div>
                <div class="stand-item-pos">📍 <?= htmlspecialchars($s['position']) ?></div>
            </div>
            <div class="stand-item-dist">--</div>
        </div>
        <?php endforeach; ?>
    </div>
    <a href="listStandsFront.php" class="back-link">← Retour au catalogue</a>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="maps_nav.js"></script>
</body>
</html>
