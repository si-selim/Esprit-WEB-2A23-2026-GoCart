<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
$totalParticipants = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE role='participant'")->fetchColumn();
$totalOrganisateurs = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE role='organisateur'")->fetchColumn();
$totalAdmins = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE role='admin'")->fetchColumn();
$withEmail = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE email IS NOT NULL AND email != ''")->fetchColumn();
$withPhone = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE tel IS NOT NULL AND tel != ''")->fetchColumn();
$withoutOccupation = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE occupation IS NULL OR occupation = ''")->fetchColumn();
$completionRate = $totalUsers > 0 ? round(($withEmail / $totalUsers) * 100) : 0;
$avgAge = $pdo->query("SELECT ROUND(AVG(age)) FROM `user` WHERE age IS NOT NULL")->fetchColumn() ?: 0;
$topCountry = $pdo->query("SELECT pays FROM `user` WHERE pays IS NOT NULL AND pays != '' GROUP BY pays ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn() ?: '-';
$topOccupation = $pdo->query("SELECT occupation FROM `user` WHERE occupation IS NOT NULL AND occupation != '' GROUP BY occupation ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn() ?: '-';

$pBar = $totalUsers > 0 ? round(($totalParticipants / $totalUsers) * 100) : 0;
$oBar = $totalUsers > 0 ? round(($totalOrganisateurs / $totalUsers) * 100) : 0;
$aBar = $totalUsers > 0 ? round(($totalAdmins / $totalUsers) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office Dashboard</title>
    <link rel="stylesheet" href="../view/assets/css/style.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-badge" src="../view/assets/images/logo_barchathon.jpg" alt="Logo Barchathon">
                <div>
                    <strong>Admin Back Office</strong><br>
                    <small>Dashboard et statistiques</small>
                </div>
            </div>
            <nav class="side-nav">
                <a class="side-link active" href="dashboard.php">Dashboard</a>
                <a class="side-link" href="backoffice_User.php">Utilisateurs</a>
                <a class="side-link" href="#">Marathons</a>
                <a class="side-link" href="#">Parcours</a>
                <a class="side-link" href="#">Rapports</a>
                <a class="side-link" href="logout.php">Deconnexion</a>
            </nav>
            <div class="side-note">
                Presentation visuelle des donnees utilisateurs avec des metriques cles et des rapports synthetiques.
            </div>
        </aside>
        <main class="content">
            <div class="mobile-nav">
                <a class="btn btn-primary" href="dashboard.php">Dashboard</a>
                <a class="btn btn-secondary" href="backoffice_User.php">Utilisateurs</a>
            </div>
            <div class="head">
                <div>
                    <h1>Dashboard Backoffice</h1>
                    <div class="muted">Visualisation des donnees utilisateurs avec graphiques et indicateurs cles pour la prise de decision.</div>
                </div>
                <div class="actions">
                    <span class="tag">Statistiques</span>
                    <span class="tag">Rapports</span>
                </div>
            </div>
            <section class="stats-grid">
                <div class="card card-hover slide-up">
                    <div class="stat"><?= $totalUsers ?></div>
                    <div>Utilisateurs inscrits</div>
                </div>
                <div class="card card-hover slide-up">
                    <div class="stat"><?= $totalParticipants ?></div>
                    <div>Participants actifs</div>
                </div>
                <div class="card card-hover slide-up">
                    <div class="stat"><?= $totalOrganisateurs ?></div>
                    <div>Organisateurs</div>
                </div>
                <div class="card card-hover slide-up">
                    <div class="stat"><?= $completionRate ?>%</div>
                    <div>Taux de profils completes</div>
                </div>
            </section>
            <section class="chart-grid">
                <div class="card chart-box fade-in">
                    <h2 class="section-title" style="margin-top:0;">Repartition des utilisateurs par role</h2>
                    <div class="chart-shell">
                        <div>
                            <div class="metric">Participants <strong><?= $totalParticipants ?></strong></div>
                            <div class="chart-bar"><span style="width:<?= $pBar ?>%"></span></div>
                        </div>
                        <div>
                            <div class="metric">Organisateurs <strong><?= $totalOrganisateurs ?></strong></div>
                            <div class="chart-bar"><span style="width:<?= $oBar ?>%"></span></div>
                        </div>
                        <div>
                            <div class="metric">Admins <strong><?= $totalAdmins ?></strong></div>
                            <div class="chart-bar"><span style="width:<?= $aBar ?>%"></span></div>
                        </div>
                        <div class="legend-row">
                            <div class="report-label">Total utilisateurs :</div>
                            <strong><?= $totalUsers ?></strong>
                        </div>
                    </div>
                </div>
                <div class="card chart-box fade-in">
                    <h2 class="section-title" style="margin-top:0;">Profils completes</h2>
                    <div class="chart-shell">
                        <div class="circle-meter"><strong><?= $completionRate ?>%</strong></div>
                        <div class="metric">Niveau global de remplissage des profils utilisateurs</div>
                        <ul class="report-list">
                            <li><span>Avec email</span><strong><?= $withEmail ?></strong></li>
                            <li><span>Avec telephone</span><strong><?= $withPhone ?></strong></li>
                            <li><span>Sans occupation</span><strong><?= $withoutOccupation ?></strong></li>
                        </ul>
                    </div>
                </div>
            </section>
            <section class="chart-grid">
                <div class="card chart-box fade-in">
                    <h2 class="section-title" style="margin-top:0;">Rapports cles</h2>
                    <ul class="report-list">
                        <li><span>Age moyen des participants</span><strong><?= $avgAge ?> ans</strong></li>
                        <li><span>Pays le plus represente</span><strong><?= htmlspecialchars($topCountry) ?></strong></li>
                        <li><span>Occupation dominante</span><strong><?= htmlspecialchars($topOccupation) ?></strong></li>
                        <li><span>Total utilisateurs</span><strong><?= $totalUsers ?></strong></li>
                    </ul>
                </div>
                <div class="card chart-box fade-in">
                    <h2 class="section-title" style="margin-top:0;">Roles</h2>
                    <ul class="report-list">
                        <li><span>Admins</span><strong><?= $totalAdmins ?></strong></li>
                        <li><span>Organisateurs</span><strong><?= $totalOrganisateurs ?></strong></li>
                        <li><span>Participants</span><strong><?= $totalParticipants ?></strong></li>
                    </ul>
                </div>
            </section>
        </main>
    </div>

    <script src="../view/assets/js/app.js"></script>
</body>
</html>
