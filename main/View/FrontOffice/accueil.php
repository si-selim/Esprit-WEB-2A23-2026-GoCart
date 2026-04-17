<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
$controller = new MarathonController();
$stats = $controller->statsNbMarathonsDispo();
$totalMarathons = count($controller->afficherMarathon());
$currentPage = 'accueil';
$user = getCurrentUser();
$role = $user['role'] ?? 'visiteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Accueil — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; --sea:#0ea5e9; --clay:#c2410c; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:#fdfaf4; }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:36px 0 0; }

        /* HERO */
        .hero {
            background:linear-gradient(135deg,#102a43 0%,#0f766e 100%);
            border-radius:32px; padding:60px 52px;
            display:grid; grid-template-columns:1.1fr .9fr; gap:44px; align-items:center;
            color:white; margin-bottom:44px; position:relative; overflow:hidden;
            box-shadow:0 24px 60px rgba(16,42,67,.18);
        }
        .hero::before { content:''; position:absolute; top:-60px; right:-60px; width:300px; height:300px; background:rgba(255,183,3,.08); border-radius:50%; }
        .hero h1 { font-size:clamp(2rem,4.5vw,3.4rem); line-height:1.05; margin-bottom:16px; }
        .hero p { opacity:.88; line-height:1.75; font-size:1.05rem; margin-bottom:30px; }
        .hero-btns { display:flex; gap:14px; flex-wrap:wrap; }
        .btn { display:inline-block; padding:14px 26px; border-radius:14px; font-weight:700; text-decoration:none; font-size:0.97rem; transition:transform .15s,box-shadow .15s; border:none; cursor:pointer; }
        .btn:hover { transform:translateY(-2px); }
        .btn-primary { background:linear-gradient(135deg,var(--sun),#f59e0b); color:var(--ink); box-shadow:0 8px 22px rgba(255,183,3,.4); }
        .btn-outline { background:rgba(255,255,255,.15); color:white; border:2px solid rgba(255,255,255,.45); }
        .hero-img img { width:100%; height:300px; object-fit:cover; border-radius:22px; box-shadow:0 16px 40px rgba(0,0,0,.3); }

        /* 4 STATS */
        .stats-section { margin-bottom:48px; }
        .stats-section h2 { font-size:1.6rem; font-weight:900; margin-bottom:22px; }
        .stats-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
        .stat-card {
            background:white; border-radius:24px; padding:30px 20px;
            text-align:center; box-shadow:0 12px 32px rgba(16,42,67,.09);
            border:1px solid rgba(16,42,67,.07); transition:transform .2s;
        }
        .stat-card:hover { transform:translateY(-3px); }
        .stat-icon { font-size:2rem; margin-bottom:10px; }
        .stat-val { font-size:2.8rem; font-weight:900; color:var(--teal); line-height:1; }
        .stat-lbl { color:#627d98; font-size:0.92rem; margin-top:8px; font-weight:600; }

        /* FEATURES */
        .features-section { margin-bottom:0; }
        .features-section h2 { font-size:1.6rem; font-weight:900; margin-bottom:22px; }
        .features { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; }
        .feature-card {
            background:white; border-radius:22px; padding:32px 24px;
            box-shadow:0 8px 24px rgba(16,42,67,.07); border:1px solid rgba(16,42,67,.06);
            text-align:center; transition:transform .2s,box-shadow .2s;
        }
        .feature-card:hover { transform:translateY(-4px); box-shadow:0 16px 36px rgba(16,42,67,.12); }
        .feature-card i { font-size:2.4rem; color:var(--teal); margin-bottom:16px; display:block; }
        .feature-card h3 { font-size:1.15rem; margin-bottom:10px; }
        .feature-card p { color:#627d98; line-height:1.7; font-size:0.93rem; }

        @media(max-width:900px){ .stats-bar{grid-template-columns:repeat(2,1fr);} }
        @media(max-width:768px){ .hero{grid-template-columns:1fr;padding:36px 24px;} .hero-img{display:none;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">

    <!-- HERO avec nouvelle photo -->
    <section class="hero">
        <div>
            <h1>Courez la Tunisie avec BarchaThon</h1>
            <p>La plateforme des marathons tunisiens. Découvrez les événements, choisissez votre parcours et vivez l'expérience unique.</p>
            <div class="hero-btns">
                <a href="listMarathons.php" class="btn btn-primary">🏃 Voir le Catalogue</a>
                <?php if ($role === 'visiteur'): ?>
                    <a href="register.php" class="btn btn-outline">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-img">
            <img src="images/hero_runner.png" alt="Marathon BarchaThon" onerror="this.src='images/img1.svg'">
        </div>
    </section>

    <!-- 4 STATS : Marathons + Places + 100% catalogue + 24/7 -->
    <section class="stats-section">
        <h2>📊 En quelques chiffres</h2>
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon">🏃</div>
                <div class="stat-val"><?php echo $totalMarathons; ?></div>
                <div class="stat-lbl">Marathons disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎟️</div>
                <div class="stat-val"><?php echo number_format((float)($stats['total_places'] ?? 0)); ?></div>
                <div class="stat-lbl">Places disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-val">100%</div>
                <div class="stat-lbl">Catalogue consultable</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🕐</div>
                <div class="stat-val">24/7</div>
                <div class="stat-lbl">Consultation visiteur</div>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="features-section">
        <h2>🏅 Pourquoi BarchaThon ?</h2>
        <div class="features">
            <div class="feature-card">
                <i class="fas fa-map-location-dot"></i>
                <h3>Parcours Détaillés</h3>
                <p>Visualisez les tracés et les points de ravitaillement pour chaque événement.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-calendar-check" style="color:var(--sea);"></i>
                <h3>Calendrier à jour</h3>
                <p>Ne ratez aucune date importante avec notre calendrier synchronisé.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-medal" style="color:var(--clay);"></i>
                <h3>Résultats Live</h3>
                <p>Consultez vos temps directement après avoir franchi la ligne d'arrivée.</p>
            </div>
        </div>
    </section>

</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
