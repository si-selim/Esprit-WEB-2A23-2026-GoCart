<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$userId = $user['id_user'] ?? $user['id'];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order_id'])) {
    $orderId = (int) $_POST['confirm_order_id'];
    $commande = $commandeC->showCommande($orderId);

    if ($commande && strtolower(trim($commande['statut'])) === 'en cours' && $commande['idutilisateur'] == $userId) {
        // Envoyer email avec lien de confirmation
        require_once '../../Controller/Mailer.php';
        $userEmail = $user['email'] ?? '';
        if ($userEmail) {
            $subject = 'Confirmer votre commande - BarchaThon';
            $confirmationLink = "http://" . $_SERVER['HTTP_HOST'] . "/Integ-standProduit/View/FrontOffice/confirm_order.php?id=" . $orderId;
            $body = "<p>Bonjour,</p>\n"
                  . "<p>Vous avez demandé à confirmer votre commande #" . $orderId . ".</p>\n"
                  . "<p>Montant : <strong>" . number_format($commande['montanttotale'], 2, ',', ' ') . " TND</strong></p>\n"
                  . "<p>Date : " . date('d/m/Y H:i', strtotime($commande['datecommande'])) . "</p>\n"
                  . "<p>Stand : #" . $commande['idstand'] . "</p>\n"
                  . "<p>Cliquez sur le lien ci-dessous pour confirmer votre commande :</p>\n"
                  . "<p><a href='" . $confirmationLink . "'>Confirmer ma commande</a></p>\n"
                  . "<p>Si vous n'avez pas demandé cette confirmation, ignorez cet email.</p>\n"
                  . "<p>Merci pour votre confiance.</p>";
            Mailer::send($userEmail, $subject, $body);
        }

        header('Location: Mes commandes.php?confirmation_sent=1');
        exit;
    }

    $deleteMessage = 'Confirmation impossible : la commande doit être en cours.';
}

if (isset($_GET['deleted'])) {
    $deleteMessage = 'Commande supprimée avec succès.';
}

if (isset($_GET['confirmed'])) {
    $deleteMessage = 'Commande confirmée avec succès. Un email de confirmation vous a été envoyé.';
}

if (isset($_GET['confirmation_sent'])) {
    $deleteMessage = 'Un email de confirmation vous a été envoyé. Veuillez vérifier votre boîte mail et cliquer sur le lien pour confirmer votre commande.';
}

$list = $commandeC->listCommandes();
$commandes = $list->fetchAll();

// Filter by current user
$commandes = array_values(array_filter($commandes, function ($commande) use ($userId) {
    return $commande['idutilisateur'] == $userId;
}));

$confirmedCount = 0;
$nonvalideCount = 0;
$confirmedAmount = 0;
$latestOrder = null;
foreach ($commandes as $commande) {
    $status = strtolower(trim($commande['statut'] ?? ''));
    if (in_array($status, ['confirmé', 'confirmé', 'validée', 'validé'])) {
        $confirmedCount++;
        $confirmedAmount += (float) ($commande['montanttotale'] ?? 0);
    }
    if ($status === 'non valide') {
        $nonvalideCount++;
    }
    if ($latestOrder === null || strtotime($commande['datecommande']) > strtotime($latestOrder['datecommande'])) {
        $latestOrder = $commande;
    }
}
$latestOrderLabel = $latestOrder ? date('d/m/Y H:i', strtotime($latestOrder['datecommande'])) . ' - ' . number_format((float) ($latestOrder['montanttotale'] ?? 0), 2, ',', ' ') . ' TND' : 'Aucune commande';

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
        .hero { position:relative; background:radial-gradient(circle at top left, rgba(255,255,255,.18), transparent 24%), linear-gradient(135deg,#102a43,#0f766e); color:#fff; border-radius:28px; padding:34px 32px; box-shadow:0 20px 40px rgba(16,42,67,.16); overflow:hidden; animation: floatUp .9s ease both; }
        .hero h1 { font-size:2.8rem; margin:0; letter-spacing:.01em; }
        .hero::before, .hero::after { content:''; position:absolute; border-radius:50%; opacity:.25; filter:blur(28px); }
        .hero::before { width:240px; height:240px; top:-50px; left:-50px; background:rgba(255,255,255,.24); }
        .hero::after { width:180px; height:180px; bottom:-40px; right:-20px; background:rgba(255,255,255,.14); }
        .panel { margin-top:18px; background:#fff; border-radius:28px; padding:24px; box-shadow:0 18px 40px rgba(16,42,67,.08); overflow:hidden; animation: fadeInUp .75s ease both; }
        .table-wrap { overflow:auto; animation: fadeInUp .8s ease both; }
        table { width:100%; border-collapse:collapse; min-width:900px; }
        th, td { padding:16px 14px; text-align:left; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
        td { color: #0f172a; }
        th { color:#475569; font-size:.9rem; text-transform:uppercase; letter-spacing:.04em; }
        .table-sort { color:#64748b; text-decoration:none; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; }
        .table-sort:hover { color:#334155; text-decoration:underline; }
        .search-input { min-width:220px; border:1px solid #cbd5e1; border-radius:14px; padding:10px 12px; transition:border-color .2s ease, box-shadow .2s ease; }
        .search-input:focus { border-color:#7dd3fc; box-shadow:0 0 0 4px rgba(59,130,246,.12); outline:none; }
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; margin:24px 0 0; }
        .stat-card { position:relative; background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.24); border-radius:18px; padding:18px 20px; box-shadow:0 10px 25px rgba(15,23,42,.08); backdrop-filter:blur(8px); overflow:hidden; }
        .stat-card strong { position:relative; z-index:1; display:block; font-size:1.9rem; color:#fff; margin-bottom:8px; }
        .stat-card small { position:relative; z-index:1; color:rgba(255,255,255,.86); }
        .stat-card:nth-child(1) { animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .stat-card:nth-child(2) { animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both; }
        .stat-card:nth-child(3) { animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both; }
        .stat-card:nth-child(4) { animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both; }
        .btn, .btn-inline { display:inline-flex; align-items:center; gap:8px; text-decoration:none; border:0; border-radius:14px; padding:11px 14px; font:inherit; font-weight:700; cursor:pointer; transition:transform .2s ease, box-shadow .2s ease, background-color .2s ease, border-color .2s ease; }
        .btn-secondary { background:#e2e8f0; color:#102a43; border:1px solid #cbd5e1; }
        .btn:hover, .btn-inline:hover { transform:translateY(-1px); box-shadow:0 12px 22px rgba(15,23,42,.12); }
        .btn-secondary:hover { background:#dbe7f1; }
        .table tbody tr { transition:background .2s ease, transform .2s ease; }
        .table tbody tr:hover { background:#f8fafc; transform:translateX(2px); }
        .panel-block { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; }
        .badge { display:inline-flex; align-items:center; padding:6px 12px; border-radius:999px; font-weight:700; }
        @keyframes fadeInUp { from { opacity:0; transform: translateY(18px);} to { opacity:1; transform: translateY(0);} }
        @keyframes floatUp { from { opacity:0; transform: translateY(12px);} to { opacity:1; transform: translateY(0);} }
        @keyframes popIn { from { opacity:0; transform:scale(.85) translateY(8px);} to { opacity:1; transform:scale(1) translateY(0);} }
        @keyframes shimmer { 0%, 100% { opacity:0; } 50% { opacity:1; } }
        @keyframes pulse { 0%, 100% { transform:scale(1);} 50% { transform:scale(1.02);} }
        .stat-card:hover { animation: pulse 2s ease-in-out infinite; }
        .badge-confirmed { background:#dcfce7; color:#166534; }
        .badge-nonvalide { background:#fee2e2; color:#991b1b; }
        .badge-waiting { background:#fef9c3; color:#92400e; }
        .badge-pending { background:#f8fafc; color:#475569; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; }
        .btn-warning { background:#fff7ed; color:#b45309; border:1px solid #fdba74; }
        .btn-delete { background:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; }
        .btn-export { background:#ecfeff; color:#0f766e; border:1px solid #99f6e4; }
        .btn-details { background:#eff6ff; color:#1d4ed8; border:1px solid #93c5fd; }
        .btn-edit { background:#ede9fe; color:#6d28d9; border:1px solid #c4b5fd; }
        .btn-disabled { background:#e2e8f0; color:#64748b; border:1px solid #cbd5e1; cursor:not-allowed; }
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
<?php require __DIR__ . '/partials/topbar.php'; ?>

    <div class="wrap">
        <section class="hero">
            <h1>Historique de mes commandes</h1>
            <div class="stats-row">
                <div class="stat-card">
                    <strong><?php echo $confirmedCount; ?></strong>
                    <small>commandes confirmées</small>
                </div>
                <div class="stat-card">
                    <strong><?php echo $nonvalideCount; ?></strong>
                    <small>commandes non valides</small>
                </div>
                <div class="stat-card">
                    <strong><?php echo number_format($confirmedAmount, 2, ',', ' ') . ' TND'; ?></strong>
                    <small>montant confirmé</small>
                </div>
                <div class="stat-card">
                    <strong><?php echo $latestOrderLabel; ?></strong>
                    <small>dernière commande</small>
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
                    <input type="text" name="search_id" class="search-input" value="<?php echo htmlspecialchars($searchId); ?>" placeholder="Rechercher par ID commande" />
                    <button type="submit" class="btn btn-details">Rechercher</button>
                    <a href="Mes commandes.php" class="btn btn-secondary">Réinitialiser</a>
                </form>
                <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                    <span style="font-weight:700; color:#102a43;">Statut :</span>
                    <?php $statusOptions = ['all' => 'Toutes', 'en cours' => 'En cours', 'confirmé' => 'Confirmé']; ?>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <a class="btn <?php echo $statusFilter === $value ? 'btn-edit' : 'btn-secondary'; ?>" href="<?php echo '?'.http_build_query(array_merge($_GET, ['status' => $value])); ?>"><?php echo $label; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="table-wrap">
                <?php
                    $dateArrow = $sort_by === 'date' ? ($sort_order === 'asc' ? '▲' : '▼') : '';
                    $montantArrow = $sort_by === 'montant' ? ($sort_order === 'asc' ? '▲' : '▼') : '';
                    $nextOrder = $sort_order === 'asc' ? 'desc' : 'asc';
                ?>
                <?php if ($totalOrders > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Numero du stand</th>
                            <th><a class="table-sort" href="<?php echo '?'.http_build_query(array_merge($_GET, ['sort_by' => 'date', 'sort_order' => $sort_by === 'date' ? $nextOrder : 'desc'])); ?>">Date <?php echo $dateArrow; ?></a></th>
                            <th><a class="table-sort" href="<?php echo '?'.http_build_query(array_merge($_GET, ['sort_by' => 'montant', 'sort_order' => $sort_by === 'montant' ? $nextOrder : 'desc'])); ?>">Montant <?php echo $montantArrow; ?></a></th>
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
                                case 'confirmé':
                                case 'confirmé':
                                case 'confirmé':
                                    $statusClass = 'badge-confirmed';
                                    $statusLabel = 'Confirmé';
                                    break;
                                case 'non valide':
                                    $statusClass = 'badge-nonvalide';
                                    $statusLabel = 'Non valide';
                                    break;
                                case 'expédiée':
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
                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn-inline btn-details" href="orderDetails.php?id=<?php echo $commande['idcommande']; ?>">
                                        Voir détails
                                    </a>
                                    <?php if (strtolower($commande['statut']) === 'en cours'): ?>
                                    <form method="post" style="display:inline-flex; margin:0;" onsubmit="return handleConfirmSubmit(this);">
                                        <input type="hidden" name="confirm_order_id" value="<?php echo htmlspecialchars($commande['idcommande']); ?>">
                                        <button type="submit" class="btn-inline btn-edit confirm-button" data-label="Confirmer" onclick="return confirm('Confirmer cette commande ? Vous recevrez un email avec un lien de confirmation.');">
                                            <span class="confirm-label">Confirmer</span>
                                        </button>
                                    </form>
                                    <form method="post" style="display:inline-flex; margin:0;">
                                        <input type="hidden" name="delete_order_id" value="<?php echo htmlspecialchars($commande['idcommande']); ?>">
                                        <button type="submit" class="btn-inline btn-delete" onclick="return confirm('Supprimer cette commande ?');">
                                            Supprimer
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if (strtolower($commande['statut']) === 'en attente de validation'): ?>
                                    <a class="btn-inline btn-edit" href="standDetails.php?id=<?php echo $commande['idstand']; ?>&amp;stand=<?php echo $commande['idstand']; ?>&amp;order=<?php echo $commande['idcommande']; ?>">
                                        Modifier
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

    <script>
        const confirmCooldownKey = 'barchathon_confirm_cooldown';
        const confirmCooldownSeconds = 30;

        function setConfirmCooldown() {
            const expiry = Date.now() + confirmCooldownSeconds * 1000;
            localStorage.setItem(confirmCooldownKey, expiry.toString());
        }

        function getConfirmCooldownRemaining() {
            const stored = localStorage.getItem(confirmCooldownKey);
            if (!stored) return 0;
            const expiry = parseInt(stored, 10);
            if (isNaN(expiry)) return 0;
            const remaining = Math.ceil((expiry - Date.now()) / 1000);
            return remaining > 0 ? remaining : 0;
        }

        function updateConfirmButtons() {
            const remaining = getConfirmCooldownRemaining();
            const buttons = document.querySelectorAll('.confirm-button');
            buttons.forEach(button => {
                const label = button.getAttribute('data-label') || 'Confirmer';
                if (remaining > 0) {
                    button.disabled = true;
                    button.classList.add('btn-disabled');
                    button.classList.remove('btn-edit');
                    button.querySelector('.confirm-label').textContent = `${label} (${remaining}s)`;
                } else {
                    button.disabled = false;
                    button.classList.remove('btn-disabled');
                    button.classList.add('btn-edit');
                    button.querySelector('.confirm-label').textContent = label;
                }
            });
            return remaining;
        }

        function startConfirmCountdown() {
            let remaining = getConfirmCooldownRemaining();
            if (remaining <= 0) return;
            updateConfirmButtons();
            const interval = setInterval(() => {
                remaining = getConfirmCooldownRemaining();
                if (remaining <= 0) {
                    clearInterval(interval);
                    updateConfirmButtons();
                    return;
                }
                updateConfirmButtons();
            }, 1000);
        }

        function handleConfirmSubmit(form) {
            setConfirmCooldown();
            startConfirmCountdown();
            return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            startConfirmCountdown();
        });
    </script>
</body>
</html>
