<?php
include '../../Controller/CommandeController.php';
include '../../Controller/LigneCommandeController.php';

$commandeC = new CommandeController();
$ligneC = new LigneCommandeController();
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    header('Location: Mes commandes.php');
    exit;
}

$commande = $commandeC->showCommande($id);
if (!$commande) {
    header('Location: Mes commandes.php');
    exit;
}

$lignesQuery = $ligneC->getLignesCommande($id);
$lignes = $lignesQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails commande #<?php echo htmlspecialchars($commande['idcommande']); ?> - BarCathon</title>
    <style>
        body { margin:0; font-family:"Segoe UI",sans-serif; background:linear-gradient(180deg,#fff8e7,#eef8f8); color:#102a43; }
        .wrap { width:min(1200px, calc(100% - 32px)); margin:24px auto 40px; }
        .hero { background:linear-gradient(135deg,#102a43,#0f766e); color:#fff; border-radius:28px; padding:28px; box-shadow:0 20px 40px rgba(16,42,67,.16); }
        .panel { margin-top:18px; background:#fff; border-radius:28px; padding:24px; box-shadow:0 18px 40px rgba(16,42,67,.08); overflow:hidden; }
        .table-wrap { overflow:auto; }
        table { width:100%; border-collapse:collapse; min-width:900px; }
        th, td { padding:16px 14px; text-align:left; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
        th { color:#486581; font-size:.9rem; text-transform:uppercase; letter-spacing:.04em; }
        .badge { display:inline-flex; align-items:center; padding:6px 12px; border-radius:999px; font-weight:700; }
        .badge-paid { background:#dcfce7; color:#166534; }
        .badge-pending { background:#fef3c7; color:#92400e; }
        .badge-valid { background:#dcfce7; color:#166534; }
        .badge-waiting { background:#fde68a; color:#92400e; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; }
        .btn, .btn-inline { display:inline-flex; align-items:center; gap:8px; text-decoration:none; border:0; border-radius:14px; padding:11px 14px; font:inherit; font-weight:700; cursor:pointer; }
        .btn-secondary { background:#f8fafc; color:#102a43; border:1px solid #cbd5e1; }
        .btn-details { background:#eff6ff; color:#1d4ed8; border:1px solid #93c5fd; }
        .card { background:#fff; border-radius:24px; box-shadow:0 20px 40px rgba(16,42,67,.08); }
        .card-body { padding:24px; }
        .section-heading { margin-bottom:18px; }
        .badge-status { padding:0.65em 0.95em; border-radius:999px; font-weight:700; }
        .table-summary th { width:200px; }
        .empty, .alert-empty { padding:30px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:22px; text-align:center; color:#486581; }
        .fo-topbar { position: sticky; top: 0; z-index: 1000; backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.82); border-bottom: 1px solid rgba(16, 42, 67, 0.08); box-shadow: 0 10px 30px rgba(16, 42, 67, 0.06); }
        .fo-topbar-shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; min-height: 78px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .fo-brand { display: inline-flex; align-items: center; gap: 12px; text-decoration: none; color: #102a43; font-weight: 900; letter-spacing: 0.04em; }
        .fo-brand-mark { width: 40px; height: 40px; border-radius: 14px; object-fit: cover; display: block; background: white; box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18); border: 1px solid rgba(16, 42, 67, 0.08); }
        .fo-brand-text { display: grid; line-height: 1.05; }
        .fo-brand-text small { color: #627d98; font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase; }
        .fo-nav { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        .fo-link, .fo-profile, .fo-cta { text-decoration: none; border-radius: 999px; padding: 10px 14px; font-weight: 700; transition: transform 0.18s ease, background 0.18s ease, box-shadow 0.18s ease; }
        .fo-link { color: #102a43; background: rgba(255, 255, 255, 0.76); border: 1px solid rgba(16, 42, 67, 0.08); }
        .fo-link:hover, .fo-profile:hover, .fo-cta:hover { transform: translateY(-1px); }
        .fo-link.active { color: white; background: linear-gradient(135deg, #102a43, #0f766e); box-shadow: 0 10px 24px rgba(16, 42, 67, 0.18); }
        .fo-cta { color: white; background: linear-gradient(135deg, #0f766e, #14b8a6); box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18); }
        .fo-profile { color: #102a43; background: linear-gradient(135deg, #fff7ed, #ffffff); border: 1px solid rgba(255, 183, 3, 0.28); }
        .fo-profile-role { display: inline-block; margin-left: 6px; padding: 3px 8px; border-radius: 999px; background: rgba(15, 118, 110, 0.1); color: #0f766e; font-size: 0.78rem; }
        @media (max-width: 900px) { .wrap { width:min(100%, calc(100% - 20px)); } .hero, .panel { padding:18px; border-radius:22px; } }
        @media (max-width: 860px) { .fo-topbar-shell { padding: 10px 0; flex-direction: column; align-items: flex-start; } .fo-nav { width: 100%; justify-content: flex-start; } }
    </style>
</head>
<body>
<div class="fo-topbar">
    <div class="fo-topbar-shell">
        <a class="fo-brand" href="index.php">
            <img class="fo-brand-mark" src="./Mes commandes_files/LOGO.jpg" alt="BarchaThon">
            <span class="fo-brand-text">
                <span>BarchaThon</span>
                <small>Front Office</small>
            </span>
        </a>
        <nav class="fo-nav">
            <a class="fo-link" href="index.php">Accueil</a>
            <a class="fo-link" href="produit.php">Catalogue</a>
            <a class="fo-link" href="notifications.php">Notifications (1)</a>
            <a class="fo-link" href="Mes commandes.php">Voir mes commandes</a>
            <a class="fo-profile" href="profile.php">Participant Demo <span class="fo-profile-role">participant</span></a>
            <a class="fo-link" href="Mes commandes.php?action=logout">Se deconnecter</a>
        </nav>
    </div>
</div>
<div class="wrap">
    <section class="hero">
        <h1>Détails de ma commande</h1>
        <p>Retrouvez le détail et les lignes associées à cette commande.</p>
    </section>
    <section class="panel">
        <div style="display:flex; flex-wrap:wrap; gap:14px; justify-content:space-between; align-items:flex-start; margin-bottom:24px;">
            <div style="min-width:280px; flex:1; background:#f8fafc; border-radius:22px; padding:22px;">
                <h2 style="margin-top:0; margin-bottom:12px; font-size:1.15rem;">Commande #<?php echo htmlspecialchars($commande['idcommande']); ?></h2>
                <p style="margin:0 0 10px;"><strong>Stand :</strong> <?php echo htmlspecialchars($commande['idstand']); ?></p>
                <p style="margin:0 0 10px;"><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['datecommande'])); ?></p>
                <p style="margin:0 0 10px;"><strong>Montant :</strong> <?php echo number_format($commande['montanttotale'], 2, ',', ' ') . ' TND'; ?></p>
                <p style="margin:0;"><strong>Statut :</strong>
                <?php
                $statusClass = 'badge-pending';
                switch (strtolower($commande['statut'])) {
                    case 'en cours': $statusClass = 'badge-waiting'; break;
                    case 'validée': $statusClass = 'badge-valid'; break;
                    case 'en attente de validation': $statusClass = 'badge-waiting'; break;
                    case 'non valide': $statusClass = 'badge-pending'; break;
                }
                ?>
                <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($commande['statut']); ?></span></p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <a href="Mes commandes.php" class="btn btn-secondary">← Retour</a>
            </div>
        </div>
        <div class="table-wrap">
            <?php if (count($lignes) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID ligne</th>
                            <th>ID produit</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Total ligne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalLignes = 0;
                        foreach ($lignes as $ligne):
                            $totalLigne = $ligne['quantite'] * $ligne['prixunitaire'];
                            $totalLignes += $totalLigne;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ligne['idligne']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['idproduit']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['quantite']); ?></td>
                            <td><?php echo number_format($ligne['prixunitaire'], 2, ',', ' ') . ' TND'; ?></td>
                            <td><?php echo number_format($totalLigne, 2, ',', ' ') . ' TND'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="4" style="text-align:right; font-weight:700;">Total des lignes</td>
                            <td><strong><?php echo number_format($totalLignes, 2, ',', ' ') . ' TND'; ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">
                    <h4>Aucune ligne de commande associée à cette commande.</h4>
                    <p>Vérifiez que la commande contient bien des produits ou retournez à la liste.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
</body>
</html>
