<?php
include '../../Controller/CommandeController.php';

$searchId = trim($_GET['search_id'] ?? '');
$sort_by = $_GET['sort_by'] ?? 'date';
$sort_order = $_GET['sort_order'] ?? 'desc';
$statusFilter = $_GET['status'] ?? 'all';

$commandeC = new CommandeController();
$deleteMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
    $orderId = (int) $_POST['delete_order_id'];
    $commande = $commandeC->showCommande($orderId);

    if ($commande && strtolower(trim($commande['statut'])) === 'en cours') {
        $commandeC->deleteCommande($orderId);
        header('Location: Mes commandes.php?deleted=1');
        exit;
    }

    $deleteMessage = 'Suppression impossible : la commande doit être en cours pour être supprimée.';
}

if (isset($_GET['deleted'])) {
    $deleteMessage = 'Commande supprimée avec succès.';
}

$list = $commandeC->listCommandes();
$commandes = $list->fetchAll();

if ($searchId !== '') {
    $commandes = array_filter($commandes, function ($commande) use ($searchId) {
        return strpos((string)$commande['idcommande'], $searchId) !== false;
    });
}

if ($statusFilter !== 'all') {
    $commandes = array_filter($commandes, function ($commande) use ($statusFilter) {
        return strtolower($commande['statut']) === strtolower($statusFilter);
    });
}

usort($commandes, function ($a, $b) use ($sort_by, $sort_order) {
    if ($sort_by === 'montant') {
        $comparison = (float) ($a['montanttotale'] ?? 0) <=> (float) ($b['montanttotale'] ?? 0);
    } else {
        $comparison = strtotime($a['datecommande']) <=> strtotime($b['datecommande']);
    }

    return $sort_order === 'asc' ? $comparison : -$comparison;
});

$totalOrders = count($commandes);
$totalAmount = 0;
foreach ($commandes as $commande) {
    $totalAmount += (float) ($commande['montanttotale'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="fr"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commandes</title>
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
        .btn, .btn-inline {
            display:inline-flex;
            align-items:center;
            gap:8px;
            text-decoration:none;
            border:0;
            border-radius:14px;
            padding:11px 14px;
            font:inherit;
            font-weight:700;
            cursor:pointer;
        }
        .btn-warning { background:#fff7ed; color:#b45309; border:1px solid #fdba74; }
        .btn-delete { background:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; }
        .btn-export { background:#ecfeff; color:#0f766e; border:1px solid #99f6e4; }
        .btn-details { background:#eff6ff; color:#1d4ed8; border:1px solid #93c5fd; }
        .btn-edit { background:#ede9fe; color:#6d28d9; border:1px solid #c4b5fd; }
        .empty { padding:30px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:22px; text-align:center; color:#486581; }
        .meta { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-top:16px; }
        .meta-card { background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.18); border-radius:18px; padding:16px; }
        @media (max-width:900px) {
            .wrap { width:min(100%, calc(100% - 20px)); }
            .hero, .panel { padding:18px; border-radius:22px; }
        }
    </style>
</head>
<body>
    <style>
    .fo-topbar {
        position: sticky;
        top: 0;
        z-index: 1000;
        backdrop-filter: blur(16px);
        background: rgba(255, 255, 255, 0.82);
        border-bottom: 1px solid rgba(16, 42, 67, 0.08);
        box-shadow: 0 10px 30px rgba(16, 42, 67, 0.06);
    }

    .fo-topbar-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
        min-height: 78px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .fo-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: #102a43;
        font-weight: 900;
        letter-spacing: 0.04em;
    }

    .fo-brand-mark {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        object-fit: cover;
        display: block;
        background: white;
        box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
        border: 1px solid rgba(16, 42, 67, 0.08);
    }

    .fo-brand-text {
        display: grid;
        line-height: 1.05;
    }

    .fo-brand-text small {
        color: #627d98;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .fo-nav {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .fo-link,
    .fo-profile,
    .fo-cta {
        text-decoration: none;
        border-radius: 999px;
        padding: 10px 14px;
        font-weight: 700;
        transition: transform 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
    }

    .fo-link {
        color: #102a43;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(16, 42, 67, 0.08);
    }

    .fo-link:hover,
    .fo-profile:hover,
    .fo-cta:hover {
        transform: translateY(-1px);
    }

    .fo-link.active {
        color: white;
        background: linear-gradient(135deg, #102a43, #0f766e);
        box-shadow: 0 10px 24px rgba(16, 42, 67, 0.18);
    }

    .fo-cta {
        color: white;
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
    }

    .fo-profile {
        color: #102a43;
        background: linear-gradient(135deg, #fff7ed, #ffffff);
        border: 1px solid rgba(255, 183, 3, 0.28);
    }

    .fo-profile-role {
        display: inline-block;
        margin-left: 6px;
        padding: 3px 8px;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.1);
        color: #0f766e;
        font-size: 0.78rem;
    }

    @media (max-width: 860px) {
        .fo-topbar-shell {
            padding: 10px 0;
            flex-direction: column;
            align-items: flex-start;
        }

        .fo-nav {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

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
            <a class="fo-link " href="index.php">Accueil</a>
            <a class="fo-link " href="produit.php">Catalogue</a>
            <a class="fo-link " href="notifications.php">Notifications (1)</a>
            <a class="fo-link active" href="Mes commandes.php">Voir mes commandes</a>
            <a class="fo-profile" href="profile.php">
                    Participant Demo                    <span class="fo-profile-role">participant</span>
            </a>
            <a class="fo-link" href="Mes commandes.php?action=logout">Se deconnecter</a>
        </nav>
    </div>
</div>
    <div class="wrap">
        <section class="hero">
            <h1>Historique de mes commandes</h1>
            <p>Participant Demo peut suivre ici toutes les commandes de stands liees a ses marathons.</p>
            <div class="meta">
                <div class="meta-card">
                    <strong><?php echo $totalOrders; ?></strong>
                    <div>commandes enregistrees</div>
                </div>
                <div class="meta-card">
                    <strong><?php echo number_format($totalAmount, 2, ',', ' ') . ' TND'; ?></strong>
                    <div>montant total des commandes</div>
                </div>
            </div>
        </section>

        <section class="panel">
            <?php if ($deleteMessage !== ''): ?>
                <div style="margin-bottom:18px; padding:16px; border-radius:18px; background:#dcfce7; color:#166534; border:1px solid #86efac;">
                    <?php echo htmlspecialchars($deleteMessage); ?>
                </div>
            <?php endif; ?>
            <div class="table-wrap" style="margin-bottom:18px; display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between;">
                <form method="get" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                    <input type="text" name="search_id" value="<?php echo htmlspecialchars($searchId); ?>" placeholder="Rechercher par ID commande" style="padding:10px 12px; border-radius:14px; border:1px solid #ccc; min-width:220px;" />
                    <button type="submit" class="btn btn-details">Rechercher</button>
                    <a href="Mes commandes.php" class="btn btn-secondary">Réinitialiser</a>
                </form>
                <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                    <span style="font-weight:700; color:#102a43;">Trier :</span>
                    <?php
                    $dateArrow = $sort_by === 'date' ? ($sort_order === 'asc' ? '▲' : '▼') : '';
                    $montantArrow = $sort_by === 'montant' ? ($sort_order === 'asc' ? '▲' : '▼') : '';
                    $nextOrder = $sort_order === 'asc' ? 'desc' : 'asc';
                    ?>
                    <a class="btn <?php echo $sort_by === 'date' ? 'btn-edit' : 'btn-details'; ?>" href="<?php echo '?'.http_build_query(array_merge($_GET, ['sort_by' => 'date', 'sort_order' => $sort_by === 'date' ? $nextOrder : 'desc'])); ?>">Date <?php echo $dateArrow; ?></a>
                    <a class="btn <?php echo $sort_by === 'montant' ? 'btn-edit' : 'btn-details'; ?>" href="<?php echo '?'.http_build_query(array_merge($_GET, ['sort_by' => 'montant', 'sort_order' => $sort_by === 'montant' ? $nextOrder : 'desc'])); ?>">Montant <?php echo $montantArrow; ?></a>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                    <span style="font-weight:700; color:#102a43;">Statut :</span>
                    <?php $statusOptions = ['all' => 'Toutes', 'en cours' => 'En cours', 'validée' => 'Validée']; ?>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <a class="btn <?php echo $statusFilter === $value ? 'btn-edit' : 'btn-secondary'; ?>" href="<?php echo '?'.http_build_query(array_merge($_GET, ['status' => $value])); ?>"><?php echo $label; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="table-wrap">
                <?php if ($totalOrders > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Numero du stand</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Paiement en ligne</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): ?>
                        <?php
                            $statusClass = 'badge-pending';
                            switch (strtolower($commande['statut'])) {
                                case 'en attente de validation':
                                    $statusClass = 'badge-waiting';
                                    $statusLabel = 'En attente';
                                    break;
                                case 'validée':
                                    $statusClass = 'badge-valid';
                                    $statusLabel = 'Validée';
                                    break;
                                case 'non valide':
                                    $statusClass = 'badge-pending';
                                    $statusLabel = 'Non valide';
                                    break;
                                case 'expédiée':
                                case 'en cours':
                                case 'en cours':
                                    $statusClass = 'badge-waiting';
                                    $statusLabel = 'En cours';
                                    break;
                                default:
                                    $statusClass = 'badge-pending';
                                    $statusLabel = $commande['statut'];
                                    break;
                            }
                        ?>
                        <tr>
                            <td>#<?php echo $commande['idcommande']; ?></td>
                            <td><?php echo $commande['idstand']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($commande['datecommande'])); ?></td>
                            <td><strong><?php echo number_format($commande['montanttotale'], 2, ',', ' ') . ' TND'; ?></strong></td>
                            <td><span class="badge badge-paid">OUI</span></td>
                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn-inline btn-details" href="orderDetails.php?id=<?php echo $commande['idcommande']; ?>">
                                        <span aria-hidden="true">🔍</span>
                                        <span>Voir details</span>
                                    </a>
                                    <?php if (strtolower($commande['statut']) === 'en cours'): ?>
                                    <form method="post" style="display:inline-flex; margin:0;">
                                        <input type="hidden" name="delete_order_id" value="<?php echo htmlspecialchars($commande['idcommande']); ?>">
                                        <button type="submit" class="btn-inline btn-delete" onclick="return confirm('Supprimer cette commande ?');">
                                            <span aria-hidden="true">🗑️</span>
                                            <span>Supprimer</span>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if (strtolower($commande['statut']) === 'en attente de validation'): ?>
                                    <a class="btn-inline btn-edit" href="standDetails.php?id=<?php echo $commande['idstand']; ?>&amp;stand=<?php echo $commande['idstand']; ?>&amp;order=<?php echo $commande['idcommande']; ?>">
                                        <span aria-hidden="true">✎</span>
                                        <span>Modifier</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty">
                    <h4>Aucune commande pour le moment</h4>
                    <p>Vous n'avez pas encore passé de commande. <a href="index.php">Retourner à l'accueil</a></p>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

</body>
</html>
