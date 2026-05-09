<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once "../../Controller/DossardController.php";
require_once "../../Controller/InscriptionController.php";

if (!isAdmin()) { header('Location: ../FrontOffice/accueil.php'); exit; }

$id = $_GET['id_inscription'] ?? 0;

$dossardController = new DossardController();
$inscriptionController = new InscriptionController();

$data = $inscriptionController->getById($id);
$nb = $data['nb_personnes'] ?? 0;
$liste = $dossardController->getByInscription($id);
$total = max($nb, count($liste));

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dossards — BarchaThon</title>
    <script>document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light');</script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .table-shell {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: white;
        }
        .table-shell table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-shell thead {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }
        .table-shell thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 16px;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            color: #374151;
        }
        .table-shell tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
        }
        .table-shell tbody tr:hover { background: #f8fafc; }

        html[data-theme="dark"] .table-shell { border-color:rgba(255,255,255,0.1); background:#1e293b; }
        html[data-theme="dark"] .table-shell thead th { background:#162032; border-bottom-color:rgba(255,255,255,0.1); color:#e2e8f0; }
        html[data-theme="dark"] .table-shell tbody td { border-bottom-color:rgba(255,255,255,0.05); color:#e2e8f0; }
        html[data-theme="dark"] .table-shell tbody tr:hover { background:#162032; }

        .color-dot {
            width: 22px; height: 22px;
            border-radius: 6px;
            margin: auto;
            border: 1px solid rgba(0,0,0,0.1);
        }
        .qr-img { width: 70px; border-radius: 8px; }
        .badge-ok { 
            background: rgba(16,185,129,.1); color:#059669;
            border: 1px solid rgba(16,185,129,.2);
            padding: 4px 10px; border-radius: 999px;
            font-size: .8rem; font-weight: 700;
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
            <small>Admin — <?php echo htmlspecialchars($user['nom']); ?></small>
        </div>
    </div>
    <nav class="side-nav">
        <a class="side-link" href="dashboard.php?tab=home">Dashboard</a>
        <a class="side-link" href="dashboard.php?tab=utilisateurs">Utilisateurs</a>
        <a class="side-link" href="dashboard.php?tab=marathons">Marathons</a>
        <a class="side-link" href="dashboard.php?tab=stands">Stands</a>
        <a class="side-link" href="dashboard.php?tab=produits">Produits</a>
        <a class="side-link" href="dashboard.php?tab=commandes">Commandes</a>
        <a class="side-link active" href="dashboard.php?tab=inscriptions_list">Inscriptions</a>
        <a class="side-link" href="../FrontOffice/accueil.php">Retour</a>
        <a class="side-link" href="../FrontOffice/logout.php">Deconnexion</a>
    </nav>
</aside>

<main class="content">

    <div class="head fade-in">
        <div>
            <h1>Dossards — Inscription #<?php echo (int)$id; ?></h1>
            <div class="muted">
                <?php echo $total; ?> dossard(s) pour cette inscription.
            </div>
        </div>
        <div class="actions">
            <a class="btn btn-secondary btn-sm" href="dashboard.php?tab=inscriptions_list">← Retour</a>
            <a class="btn btn-primary btn-sm" 
               href="../FrontOffice/export_pdf.php?id_inscription=<?php echo (int)$id; ?>">
               Exporter PDF
            </a>
        </div>
    </div>

    <?php if ($nb == 0): ?>
        <div class="card fade-in" style="padding:30px; text-align:center; color:var(--coral);">
            Aucune inscription trouvée.
        </div>
    <?php else: ?>

    <section class="section-card fade-in">
        <h2 class="section-title">Liste des dossards</h2>

        <?php if (count($liste) === 0): ?>
            <div style="padding:20px; color:var(--coral);">Aucun dossard trouvé pour cette inscription.</div>
        <?php endif; ?>

        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th>Nom</th>
                        <th>Numéro</th>
                        <th>Taille</th>
                        <th>Couleur</th>
                        <th>QR Code</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $total; $i++): ?>
                    <tr>
                        <td>
                            <?php if (isset($liste[$i])): ?>
                                <span class="badge-ok">✔ Complété</span>
                            <?php else: ?>
                                <a href="dossard.php?id_inscription=<?php echo (int)$id; ?>" 
                                   class="btn btn-warning btn-sm">Compléter</a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($liste[$i]['nom'] ?? '—'); ?></td>
                        <td><strong><?php echo htmlspecialchars($liste[$i]['numero'] ?? ($i + 1)); ?></strong></td>
                        <td><?php echo htmlspecialchars($liste[$i]['taille'] ?? '—'); ?></td>
                        <td>
                            <?php if (isset($liste[$i]['couleur'])): ?>
                                <div class="color-dot" style="background:<?php echo htmlspecialchars($liste[$i]['couleur']); ?>"></div>
                                <small><?php echo htmlspecialchars($liste[$i]['couleur']); ?></small>
                            <?php else: echo '—'; endif; ?>
                        </td>
                        <td>
                            <?php if (isset($liste[$i]['qr_code'])): ?>
                                <img class="qr-img" src="../../qr/<?php echo htmlspecialchars($liste[$i]['qr_code']); ?>" alt="QR">
                            <?php else: ?>
                                <span class="tag" style="background:#fee2e2;color:#b42318;">Pas de QR</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <div class="section-note">Total : <?php echo $total; ?> dossard(s).</div>
    </section>

    <?php endif; ?>

</main>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>