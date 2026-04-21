<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../Controller/UserController.php';
require_once __DIR__ . '/../../Controller/CommandeController.php';
require_once __DIR__ . '/../../Controller/LigneCommandeController.php';

if (!isAdmin()) { header('Location: ../FrontOffice/accueil.php'); exit; }

$mCtrl = new MarathonController();
$pCtrl = new ParcoursController();
$uCtrl = new UserController();
$cCtrl = new CommandeController();
$lCtrl = new LigneCommandeController();

if (isset($_GET['del_m'])) { $mCtrl->supprimerMarathon((int)$_GET['del_m']); header('Location: dashboard.php?tab=marathons'); exit; }
if (isset($_GET['del_p'])) { $pCtrl->supprimerParcours((int)$_GET['del_p']); header('Location: dashboard.php?tab=parcours'); exit; }
if (isset($_GET['del_u'])) {
    $uCtrl->supprimerUser((int)$_GET['del_u']);
    header('Location: dashboard.php?tab=utilisateurs'); exit;
}

if (isset($_GET['delete_commande'])) {
    $cCtrl->deleteCommande((int) $_GET['delete_commande']);
    header('Location: dashboard.php?tab=commandes');
    exit;
}

if (isset($_GET['delete_ligne'])) {
    $lCtrl->deleteLigneCommande((int) $_GET['delete_ligne']);
    $redirectParams = [];
    if (!empty($_GET['view_commande'])) {
        $redirectParams[] = 'view_commande=' . urlencode($_GET['view_commande']);
    }
    if (!empty($_GET['search_lignes'])) {
        $redirectParams[] = 'search_lignes=' . urlencode($_GET['search_lignes']);
    }
    if (!empty($_GET['sort_lignes'])) {
        $redirectParams[] = 'sort_lignes=' . urlencode($_GET['sort_lignes']);
    }
    if (!empty($_GET['dir_lignes'])) {
        $redirectParams[] = 'dir_lignes=' . urlencode($_GET['dir_lignes']);
    }
    header('Location: dashboard.php?tab=commandes' . (!empty($redirectParams) ? '&' . implode('&', $redirectParams) : ''));
    exit;
}

$activeTab = $_GET['tab'] ?? 'home';
$marathons  = $mCtrl->afficherMarathon();
$parcours   = $pCtrl->afficherParcours();
$statsM     = $mCtrl->statsNbMarathonsDispo();
$statsP     = $pCtrl->statsParcours();
$regions    = $mCtrl->getRegions();

$searchM = $_GET['searchM'] ?? '';
$filterRegion = $_GET['region'] ?? '';
if ($searchM !== '') $marathons = $mCtrl->rechercherMarathon($searchM);
elseif ($filterRegion !== '') $marathons = $mCtrl->filtrerMarathon($filterRegion);

$searchP = $_GET['searchP'] ?? '';
$filterDiff = $_GET['difficulte'] ?? '';
if ($searchP !== '') $parcours = $pCtrl->rechercherParcoursParNom($searchP);
elseif ($filterDiff !== '') $parcours = $pCtrl->filtrerParcours($filterDiff);

$searchU = trim($_GET['searchU'] ?? '');
$filterRole = trim($_GET['filterRole'] ?? '');
$filterPays = trim($_GET['filterPays'] ?? '');
$sortBy = $_GET['sort'] ?? '';
$sortDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';
$totalUsers = $uCtrl->countUsers($searchU, $filterRole, $filterPays);
$dbUsers = $uCtrl->rechercherUsers($searchU, $filterRole, $filterPays, $sortBy, $sortDir);
$uPaysList = $uCtrl->getPays();

$dashStats = $uCtrl->statsUsers();
$dashTotalUsers = $dashStats['total'];
$dashParticipants = $dashStats['participants'];
$dashOrganisateurs = $dashStats['organisateurs'];
$dashAdmins = $dashStats['admins'];
$dashWithEmail = $dashStats['withEmail'];
$dashWithPhone = $dashStats['withPhone'];
$dashWithoutOccupation = $dashStats['withoutOccupation'];
$dashCompletionRate = $dashStats['completionRate'];
$dashAvgAge = $dashStats['avgAge'];
$dashTopCountry = $dashStats['topCountry'];
$dashTopOccupation = $dashStats['topOccupation'];
$dashPBar = $dashStats['pBar'];
$dashOBar = $dashStats['oBar'];
$dashABar = $dashStats['aBar'];

$searchC = trim($_GET['searchC'] ?? '');
$sortC = $_GET['sortC'] ?? 'datecommande';
$dirC = $_GET['dirC'] ?? 'DESC';
$viewCommande = $_GET['view_commande'] ?? null;
$searchL = trim($_GET['searchL'] ?? '');
$sortL = $_GET['sortL'] ?? 'idligne';
$dirL = $_GET['dirL'] ?? 'ASC';

$listCommandes = $cCtrl->listCommandesFiltered($searchC, $sortC, $dirC);
$lignes = $lCtrl->listLignesCommandeFiltered($viewCommande, $searchL, $sortL, $dirL);
$lignes = $lignes->fetchAll();

$user = getCurrentUser();
$currentPage = 'dashboard';

if ($viewCommande) {
    $commande = $cCtrl->showCommande($viewCommande);
    if (!$commande) {
        header('Location: dashboard.php?tab=commandes');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détails commande #<?php echo htmlspecialchars($commande['idcommande']); ?> - Admin</title>
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
        .btn-danger { background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; }
        .card { background:#fff; border-radius:24px; box-shadow:0 20px 40px rgba(16,42,67,.08); }
        .card-body { padding:24px; }
        .section-heading { margin-bottom:18px; }
        .badge-status { padding:0.65em 0.95em; border-radius:999px; font-weight:700; }
        .table-summary th { width:200px; }
        .empty, .alert-empty { padding:30px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:22px; text-align:center; color:#486581; }
        </style>
    </head>
    <body>
    <div class="wrap">
        <section class="hero">
            <h1>Détails de la commande #<?php echo htmlspecialchars($commande['idcommande']); ?> (Admin)</h1>
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
                    <a href="dashboard.php?tab=commandes" class="btn btn-secondary">← Retour au dashboard</a>
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
                                <th>Actions</th>
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
                                <td>
                                    <a href="?tab=commandes&delete_ligne=<?php echo $ligne['idligne']; ?>&view_commande=<?php echo urlencode($viewCommande); ?>&searchL=<?php echo urlencode($searchL); ?>&sortL=<?php echo urlencode($sortL); ?>&dirL=<?php echo urlencode($dirL); ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette ligne de commande ?');">Supprimer</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="5" style="text-align:right; font-weight:700;">Total des lignes</td>
                                <td><strong><?php echo number_format($totalLignes, 2, ',', ' ') . ' TND'; ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty">
                        <h4>Aucune ligne de commande associée à cette commande.</h4>
                        <p>Vérifiez que la commande contient bien des produits ou retournez au dashboard.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dashboard Admin — BarchaThon</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tag-no { background: rgba(239,68,68,.08); color: var(--coral); border-color: rgba(239,68,68,.15); }
        .tag-easy { background: rgba(16,185,129,.08); color: #059669; border-color: rgba(16,185,129,.15); }
        .tag-med { background: rgba(245,158,11,.08); color: #d97706; border-color: rgba(245,158,11,.15); }
        .tag-hard { background: rgba(239,68,68,.08); color: var(--coral); border-color: rgba(239,68,68,.15); }
        .marathon-img { width: 54px; height: 38px; object-fit: cover; border-radius: 10px; }
        .table-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn-sm { padding: 8px 14px !important; font-size: .85rem !important; }
        .btn-pdf { background: var(--nav); color: #fff; box-shadow: 0 4px 16px rgba(15,23,42,.2); }
        .btn-pdf:hover { box-shadow: 0 10px 28px rgba(15,23,42,.3); }
        span.user-thumb {
            background: linear-gradient(135deg, var(--teal), var(--sun));
            color: #fff; font-weight: 900; font-size: .8rem; border-radius: 50%;
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
        <a class="side-link <?php echo $activeTab==='home'?'active':''; ?>" href="dashboard.php?tab=home">Dashboard</a>
        <a class="side-link <?php echo $activeTab==='utilisateurs'?'active':''; ?>" href="dashboard.php?tab=utilisateurs">Utilisateurs</a>
        <a class="side-link <?php echo $activeTab==='stands'?'active':''; ?>" href="dashboard.php?tab=stands">Stands</a>
        <a class="side-link <?php echo $activeTab==='commandes'?'active':''; ?>" href="dashboard.php?tab=commandes">Commandes</a>
        <a class="side-link <?php echo $activeTab==='sponsors'?'active':''; ?>" href="dashboard.php?tab=sponsors">Sponsors</a>
        <a class="side-link" href="../FrontOffice/accueil.php">Retour</a>
        <a class="side-link" href="../FrontOffice/logout.php">Deconnexion</a>
    </nav>
    <div class="side-note">Administration BarchaThon — gestion des marathons, parcours et utilisateurs.</div>
</aside>

<main class="content">

<?php if ($activeTab === 'home'): ?>

    <div class="mobile-nav">
        <a class="btn btn-primary" href="dashboard.php?tab=home">Dashboard</a>
        <a class="btn btn-secondary" href="dashboard.php?tab=utilisateurs">Utilisateurs</a>
        <a class="btn btn-secondary" href="dashboard.php?tab=commandes">Commandes</a>
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
            <div class="stat"><?php echo $dashTotalUsers; ?></div>
            <div>Utilisateurs inscrits</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat"><?php echo $dashParticipants; ?></div>
            <div>Participants actifs</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat"><?php echo $dashOrganisateurs; ?></div>
            <div>Organisateurs</div>
        </div>
        <div class="card card-hover slide-up">
            <div class="stat"><?php echo $dashCompletionRate; ?>%</div>
            <div>Taux de profils completes</div>
        </div>
    </section>
    <section class="chart-grid">
        <div class="card chart-box fade-in">
            <h2 class="section-title" style="margin-top:0;">Repartition des utilisateurs par role</h2>
            <div class="chart-shell">
                <div>
                    <div class="metric">Participants <strong><?php echo $dashParticipants; ?></strong></div>
                    <div class="chart-bar"><span style="width:<?php echo $dashPBar; ?>%"></span></div>
                </div>
                <div>
                    <div class="metric">Organisateurs <strong><?php echo $dashOrganisateurs; ?></strong></div>
                    <div class="chart-bar"><span style="width:<?php echo $dashOBar; ?>%"></span></div>
                </div>
                <div>
                    <div class="metric">Admins <strong><?php echo $dashAdmins; ?></strong></div>
                    <div class="chart-bar"><span style="width:<?php echo $dashABar; ?>%"></span></div>
                </div>
                <div class="legend-row">
                    <div class="report-label">Total utilisateurs :</div>
                    <strong><?php echo $dashTotalUsers; ?></strong>
                </div>
            </div>
        </div>
        <div class="card chart-box fade-in">
            <h2 class="section-title" style="margin-top:0;">Profils completes</h2>
            <div class="chart-shell">
                <div class="circle-meter"><strong><?php echo $dashCompletionRate; ?>%</strong></div>
                <div class="metric">Niveau global de remplissage des profils utilisateurs</div>
                <ul class="report-list">
                    <li><span>Avec email</span><strong><?php echo $dashWithEmail; ?></strong></li>
                    <li><span>Avec telephone</span><strong><?php echo $dashWithPhone; ?></strong></li>
                    <li><span>Sans occupation</span><strong><?php echo $dashWithoutOccupation; ?></strong></li>
                </ul>
            </div>
        </div>
    </section>
    <section class="chart-grid">
        <div class="card chart-box fade-in">
            <h2 class="section-title" style="margin-top:0;">Rapports cles</h2>
            <ul class="report-list">
                <li><span>Age moyen des participants</span><strong><?php echo $dashAvgAge; ?> ans</strong></li>
                <li><span>Pays le plus represente</span><strong><?php echo htmlspecialchars($dashTopCountry); ?></strong></li>
                <li><span>Occupation dominante</span><strong><?php echo htmlspecialchars($dashTopOccupation); ?></strong></li>
                <li><span>Total utilisateurs</span><strong><?php echo $dashTotalUsers; ?></strong></li>
            </ul>
        </div>
        <div class="card chart-box fade-in">
            <h2 class="section-title" style="margin-top:0;">Roles</h2>
            <ul class="report-list">
                <li><span>Admins</span><strong><?php echo $dashAdmins; ?></strong></li>
                <li><span>Organisateurs</span><strong><?php echo $dashOrganisateurs; ?></strong></li>
                <li><span>Participants</span><strong><?php echo $dashParticipants; ?></strong></li>
            </ul>
        </div>
    </section>

<?php elseif ($activeTab === 'utilisateurs'): ?>

    <div class="head fade-in">
        <div>
            <h1>Section utilisateurs</h1>
            <div class="muted">Vue administrative pour consulter et gerer les utilisateurs. <?php echo $totalUsers; ?> utilisateur(s) au total.</div>
        </div>
        <div class="actions">
            <a class="btn btn-secondary btn-sm" href="../FrontOffice/export_users.php?search=<?php echo urlencode($searchU); ?>&role=<?php echo urlencode($filterRole); ?>&pays=<?php echo urlencode($filterPays); ?>">Exporter CSV</a>
            <span class="tag"><?php echo $totalUsers; ?> utilisateurs</span>
        </div>
    </div>

    <section class="section-card fade-in">
        <h2 class="section-title">Utilisateurs</h2>
        <form method="GET" action="">
            <input type="hidden" name="tab" value="utilisateurs">
            <div class="toolbar">
                <div class="search-box">
                    <input type="search" name="searchU" placeholder="Rechercher un utilisateur, un email ou un pays" value="<?php echo htmlspecialchars($searchU); ?>">
                </div>
                <div class="filter-group">
                    <label>
                        Trier par
                        <select name="sort" onchange="this.form.submit()">
                            <option value="">Par defaut</option>
                            <option value="nom_complet" <?php echo $sortBy==='nom_complet'?'selected':''; ?>>Nom</option>
                            <option value="age" <?php echo $sortBy==='age'?'selected':''; ?>>Age</option>
                            <option value="poids" <?php echo $sortBy==='poids'?'selected':''; ?>>Poids</option>
                            <option value="taille" <?php echo $sortBy==='taille'?'selected':''; ?>>Taille</option>
                            <option value="ville" <?php echo $sortBy==='ville'?'selected':''; ?>>Ville</option>
                        </select>
                    </label>
                    <label>
                        Ordre
                        <select name="dir" onchange="this.form.submit()">
                            <option value="asc" <?php echo $sortDir==='ASC'?'selected':''; ?>>Croissant</option>
                            <option value="desc" <?php echo $sortDir==='DESC'?'selected':''; ?>>Decroissant</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par role
                        <select name="filterRole" onchange="this.form.submit()">
                            <option value="">Tout</option>
                            <option value="participant" <?php echo $filterRole==='participant'?'selected':''; ?>>Participant</option>
                            <option value="organisateur" <?php echo $filterRole==='organisateur'?'selected':''; ?>>Organisateur</option>
                            <option value="admin" <?php echo $filterRole==='admin'?'selected':''; ?>>Admin</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par pays
                        <select name="filterPays" onchange="this.form.submit()">
                            <option value="">Tout</option>
                            <?php foreach ($uPaysList as $p): ?>
                                <option value="<?php echo htmlspecialchars($p); ?>" <?php echo $filterPays===$p?'selected':''; ?>><?php echo htmlspecialchars($p); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </div>
        </form>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Nom utilisateur</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Pays</th>
                        <th>Ville / zone</th>
                        <th>Telephone</th>
                        <th>Occupation</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dbUsers)): ?>
                        <tr><td colspan="11" style="text-align:center;color:var(--muted);">Aucun utilisateur trouve.</td></tr>
                    <?php else: foreach ($dbUsers as $u): ?>
                    <tr>
                        <td><?php echo $u['id_user']; ?></td>
                        <td>
                            <?php if (!empty($u['profile_picture'])): ?>
                                <img class="user-thumb" src="../FrontOffice/images/uploads/<?php echo htmlspecialchars($u['profile_picture']); ?>" alt="">
                            <?php else: ?>
                                <span class="user-thumb"><?php echo mb_strtoupper(mb_substr($u['nom_complet'], 0, 1)); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($u['nom_complet']); ?></td>
                        <td><?php echo htmlspecialchars($u['nom_user']); ?></td>
                        <td><span class="tag"><?php echo htmlspecialchars($u['role']); ?></span></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['pays'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['ville'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['tel'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['occupation'] ?? '-'); ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary btn-sm" href="../FrontOffice/edit_user.php?id=<?php echo $u['id_user']; ?>">Modifier</a>
                                <button class="btn btn-danger btn-sm" onclick="showConfirm('Supprimer l\'utilisateur <?php echo htmlspecialchars(addslashes($u['nom_complet'])); ?> ?', function(){ document.getElementById('del-<?php echo $u['id_user']; ?>').submit(); });">Supprimer</button>
                                <form id="del-<?php echo $u['id_user']; ?>" method="GET" action="dashboard.php" style="display:none;">
                                    <input type="hidden" name="del_u" value="<?php echo $u['id_user']; ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <div class="section-note">Total : <?php echo $totalUsers; ?> utilisateur(s).</div>
    </section>

<?php elseif ($activeTab === 'marathons'): ?>

    <div class="head fade-in">
        <div>
            <h1>Gestion des Marathons</h1>
            <div class="muted">Liste complete — consultation et suppression uniquement.</div>
        </div>
        <a class="btn btn-pdf btn-sm" href="#"><i class="fas fa-file-pdf"></i> Exporter PDF</a>
    </div>

    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="stat-val"><?php echo count($mCtrl->afficherMarathon()); ?></div>
            <div class="stat-lbl">Marathons affiches</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?php echo number_format((float)($statsM['total_places']??0)); ?></div>
            <div class="stat-lbl">Places disponibles</div>
        </div>
    </div>

    <section class="section-card fade-in">
        <form method="GET" id="fmM" class="toolbar">
            <input type="hidden" name="tab" value="marathons">
            <div class="search-box">
                <input type="text" name="searchM" id="sM" placeholder="Rechercher par nom..." value="<?php echo htmlspecialchars($searchM); ?>">
            </div>
            <div class="filter-group">
                <label>
                    Region
                    <select name="region" id="rM">
                        <option value="">Toutes les regions</option>
                        <?php foreach ($regions as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $filterRegion===$r?'selected':''; ?>><?php echo htmlspecialchars($r); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
        </form>
    </section>

    <section class="section-card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 class="section-title" style="margin:0;">Liste des Marathons</h2>
            <span class="tag"><?php echo count($marathons); ?> resultats</span>
        </div>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Nom du Marathon</th>
                        <th>Organisateur</th>
                        <th>Region</th>
                        <th>Date</th>
                        <th>Places dispo</th>
                        <th>Prix (TND)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($marathons)): ?>
                        <tr><td colspan="9" style="text-align:center;color:var(--muted);">Aucun marathon trouve.</td></tr>
                    <?php else: foreach($marathons as $m2): ?>
                    <tr>
                        <td><strong>#<?php echo $m2['id_marathon']; ?></strong></td>
                        <td>
                            <img class="marathon-img"
                                 src="../FrontOffice/<?php echo htmlspecialchars($m2['image_marathon']); ?>"
                                 onerror="this.src='../FrontOffice/images/img1.svg'" alt="">
                        </td>
                        <td><strong><?php echo htmlspecialchars($m2['nom_marathon']); ?></strong></td>
                        <td><?php echo htmlspecialchars($m2['organisateur_marathon']); ?></td>
                        <td><span class="tag"><?php echo htmlspecialchars($m2['region_marathon']); ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($m2['date_marathon'])); ?></td>
                        <td>
                            <?php if($m2['nb_places_dispo'] > 0): ?>
                                <span class="tag"><?php echo $m2['nb_places_dispo']; ?></span>
                            <?php else: ?>
                                <span class="tag tag-no">Complet</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo number_format($m2['prix_marathon'],2); ?></strong></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary btn-sm" href="../FrontOffice/detailMarathon.php?id=<?php echo $m2['id_marathon']; ?>">Voir détails</a>
                                <button class="btn btn-danger btn-sm" onclick="askDelM(<?php echo $m2['id_marathon']; ?>, '<?php echo htmlspecialchars($m2['nom_marathon'], ENT_QUOTES); ?>')">Supprimer</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($activeTab === 'parcours'): ?>

    <div class="head fade-in">
        <div>
            <h1>Gestion des Parcours</h1>
            <div class="muted">Liste complete — consultation et suppression uniquement.</div>
        </div>
        <a class="btn btn-pdf btn-sm" href="#"><i class="fas fa-file-pdf"></i> Exporter PDF</a>
    </div>

    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="stat-val"><?php echo (int)($statsP['total']??0); ?></div>
            <div class="stat-lbl">Total Parcours</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #059669;">
            <div class="stat-val" style="color:#059669;"><?php echo (int)($statsP['facile']??0); ?></div>
            <div class="stat-lbl">Faciles</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #d97706;">
            <div class="stat-val" style="color:#d97706;"><?php echo (int)($statsP['moyen']??0); ?></div>
            <div class="stat-lbl">Moyens</div>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--coral);">
            <div class="stat-val" style="color:var(--coral);"><?php echo (int)($statsP['difficile']??0); ?></div>
            <div class="stat-lbl">Difficiles</div>
        </div>
    </div>

    <section class="section-card fade-in">
        <form method="GET" id="fmP" class="toolbar">
            <input type="hidden" name="tab" value="parcours">
            <div class="search-box">
                <input type="text" name="searchP" id="sP" placeholder="Rechercher par nom de parcours..." value="<?php echo htmlspecialchars($searchP); ?>">
            </div>
            <div class="filter-group">
                <label>
                    Difficulte
                    <select name="difficulte" id="dP">
                        <option value="">Toutes</option>
                        <option value="facile" <?php echo $filterDiff==='facile'?'selected':''; ?>>Facile</option>
                        <option value="moyen" <?php echo $filterDiff==='moyen'?'selected':''; ?>>Moyen</option>
                        <option value="difficile" <?php echo $filterDiff==='difficile'?'selected':''; ?>>Difficile</option>
                    </select>
                </label>
            </div>
        </form>
    </section>

    <section class="section-card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 class="section-title" style="margin:0;">Liste des Parcours</h2>
            <span class="tag"><?php echo count($parcours); ?> resultats</span>
        </div>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Parcours</th>
                        <th>Point Depart</th>
                        <th>Point Arrivee</th>
                        <th>Distance (km)</th>
                        <th>Difficulte</th>
                        <th>Marathon</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($parcours)): ?>
                        <tr><td colspan="8" style="text-align:center;color:var(--muted);">Aucun parcours trouve.</td></tr>
                    <?php else: foreach($parcours as $p2):
                        $dc = ['facile'=>'tag-easy','moyen'=>'tag-med','difficile'=>'tag-hard'][$p2['difficulte']]??'';
                    ?>
                    <tr>
                        <td><strong>#<?php echo $p2['id_parcours']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($p2['nom_parcours']); ?></strong></td>
                        <td><?php echo htmlspecialchars($p2['point_depart']); ?></td>
                        <td><?php echo htmlspecialchars($p2['point_arrivee']); ?></td>
                        <td><strong><?php echo number_format((float)$p2['distance'],2); ?> km</strong></td>
                        <td><span class="tag <?php echo $dc; ?>"><?php echo htmlspecialchars($p2['difficulte']); ?></span></td>
                        <td><span class="tag"><?php echo htmlspecialchars($p2['nom_marathon']); ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary btn-sm" href="../FrontOffice/detailMarathon.php?id=<?php echo $p2['id_marathon']; ?>">Voir détails</a>
                                <button class="btn btn-danger btn-sm" onclick="askDelP(<?php echo $p2['id_parcours']; ?>, '<?php echo htmlspecialchars($p2['nom_parcours'], ENT_QUOTES); ?>')">Supprimer</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($activeTab === 'stands'): ?>

    <div class="head fade-in">
        <div>
            <h1>Stands</h1>
            <div class="muted">Liste des stands partenaires.</div>
        </div>
    </div>
    <section class="section-card fade-in">
        <p style="color:var(--muted);padding:20px;text-align:center;">Module Stands en cours d'integration.</p>
    </section>

<?php elseif ($activeTab === 'commandes'): ?>

    <div class="head fade-in">
        <div>
            <h1>Commandes</h1>
            <div class="muted">Gestion des commandes et lignes de commandes.</div>
        </div>
    </div>

    <section class="section-card fade-in">
        <h2 class="section-title">Toutes les commandes</h2>
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <input type="hidden" name="tab" value="commandes">
                <input type="text" name="searchC" value="<?php echo htmlspecialchars($searchC); ?>" placeholder="Rechercher commandes..." style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;" />
                <select name="sortC" style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;">
                    <option value="datecommande"<?php echo $sortC === 'datecommande' ? ' selected' : ''; ?>>Date</option>
                    <option value="montanttotale"<?php echo $sortC === 'montanttotale' ? ' selected' : ''; ?>>Montant</option>
                    <option value="idcommande"<?php echo $sortC === 'idcommande' ? ' selected' : ''; ?>>ID</option>
                </select>
                <select name="dirC" style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;">
                    <option value="DESC"<?php echo $dirC === 'DESC' ? ' selected' : ''; ?>>Décroissant</option>
                    <option value="ASC"<?php echo $dirC === 'ASC' ? ' selected' : ''; ?>>Croissant</option>
                </select>
                <?php if (!empty($viewCommande)): ?>
                    <input type="hidden" name="view_commande" value="<?php echo htmlspecialchars($viewCommande); ?>" />
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Appliquer</button>
                <a href="dashboard.php?tab=commandes" class="btn btn-secondary">Réinitialiser</a>
            </form>
            <?php if (!empty($viewCommande)): ?>
            <div style="align-self:center; font-weight:700;">Affichage des lignes pour commande #<?php echo htmlspecialchars($viewCommande); ?></div>
            <?php endif; ?>
        </div>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Participant</th>
                        <th>Stand</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Paiement en ligne</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listCommandes as $commande): ?>
                        <?php
                        $statut_label = '';
                        switch($commande['statut']) {
                            case 'en attente de validation':
                                $statut_label = 'en attente';
                                break;
                            case 'validée':
                                $statut_label = 'validé';
                                break;
                            case 'annulée':
                                $statut_label = 'annulé';
                                break;
                            case 'non valide':
                                $statut_label = 'non valide';
                                break;
                            case 'EN COURS':
                                $statut_label = 'en cours';
                                break;
                            default:
                                $statut_label = $commande['statut'];
                                break;
                        }
                        ?>
                        <tr>
                            <td>#<?php echo $commande['idcommande']; ?></td>
                            <td>Utilisateur <?php echo $commande['idutilisateur']; ?></td>
                            <td>Stand <?php echo $commande['idstand']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($commande['datecommande'])); ?></td>
                            <td><?php echo number_format($commande['montanttotale'], 2, ',', ' '); ?> TND</td>
                            <td>OUI</td>
                            <td><?php echo htmlspecialchars($statut_label); ?></td>
                            <td>
                                <a href="orderDetailsAdmin.php?idcommande=<?php echo $commande['idcommande']; ?>" class="btn btn-secondary" style="margin-right:8px;">Voir details</a>
                                <a href="?tab=commandes&delete_commande=<?php echo $commande['idcommande']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette commande ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="section-card fade-in">
        <h2 class="section-title">Toutes les lignes de commandes</h2>
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <input type="hidden" name="tab" value="commandes">
                <input type="text" name="searchL" value="<?php echo htmlspecialchars($searchL); ?>" placeholder="Rechercher lignes..." style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;" />
                <select name="sortL" style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;">
                    <option value="idligne"<?php echo $sortL === 'idligne' ? ' selected' : ''; ?>>ID Ligne</option>
                    <option value="idcommande"<?php echo $sortL === 'idcommande' ? ' selected' : ''; ?>>ID Commande</option>
                    <option value="quantite"<?php echo $sortL === 'quantite' ? ' selected' : ''; ?>>Quantité</option>
                    <option value="prixunitaire"<?php echo $sortL === 'prixunitaire' ? ' selected' : ''; ?>>Prix</option>
                </select>
                <select name="dirL" style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;">
                    <option value="ASC"<?php echo $dirL === 'ASC' ? ' selected' : ''; ?>>Croissant</option>
                    <option value="DESC"<?php echo $dirL === 'DESC' ? ' selected' : ''; ?>>Décroissant</option>
                </select>
                <?php if (!empty($viewCommande)): ?>
                    <input type="hidden" name="view_commande" value="<?php echo htmlspecialchars($viewCommande); ?>" />
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Appliquer</button>
                <a href="dashboard.php?tab=commandes<?php echo !empty($viewCommande) ? '&view_commande=' . urlencode($viewCommande) : ''; ?>" class="btn btn-secondary">Réinitialiser</a>
            </form>
        </div>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>ID Ligne</th>
                        <th>ID Commande</th>
                        <th>ID Produit</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire (TND)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lignes as $ligne): ?>
                        <tr>
                            <td><?php echo $ligne['idligne']; ?></td>
                            <td><?php echo $ligne['idcommande']; ?></td>
                            <td><?php echo $ligne['idproduit']; ?></td>
                            <td><?php echo $ligne['quantite']; ?></td>
                            <td><?php echo number_format($ligne['prixunitaire'], 2, ',', ' '); ?> TND</td>
                            <td>
                                <a href="?tab=commandes&delete_ligne=<?php echo $ligne['idligne']; ?>&view_commande=<?php echo urlencode($viewCommande); ?>&searchL=<?php echo urlencode($searchL); ?>&sortL=<?php echo urlencode($sortL); ?>&dirL=<?php echo urlencode($dirL); ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette ligne de commande ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($activeTab === 'sponsors'): ?>

    <div class="head fade-in">
        <div>
            <h1>Sponsors</h1>
            <div class="muted">Liste des sponsors partenaires.</div>
        </div>
    </div>
    <section class="section-card fade-in">
        <p style="color:var(--muted);padding:20px;text-align:center;">Module Sponsors en cours d'integration.</p>
    </section>

<?php endif; ?>

</main>
</div>

<div id="confirm-modal" class="modal-overlay">
    <div class="modal-box">
        <div style="font-size:3rem;margin-bottom:12px;">🗑️</div>
        <h3>Confirmation</h3>
        <p id="confirm-message" style="color:#627d98;margin-bottom:0;"></p>
        <div class="modal-actions">
            <button id="confirm-yes" class="btn btn-danger">Oui, supprimer</button>
            <button class="btn btn-secondary" data-modal-close>Annuler</button>
        </div>
    </div>
</div>

<div id="feedback-modal" class="modal-overlay">
    <div class="modal-box">
        <div id="feedback-icon" class="feedback-icon success"></div>
        <p id="feedback-message"></p>
    </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
<?php if ($activeTab === 'marathons'): ?>
document.getElementById('rM').addEventListener('change', function(){ document.getElementById('fmM').submit(); });
var tM;
document.getElementById('sM').addEventListener('input', function(){
    clearTimeout(tM); tM = setTimeout(function(){ document.getElementById('fmM').submit(); }, 500);
});
function askDelM(id, nom) {
    document.getElementById('confirm-message').textContent = 'Supprimer le marathon "' + nom + '" ?';
    document.getElementById('confirm-yes').onclick = function() {
        window.location.href = 'dashboard.php?tab=marathons&del_m=' + id;
    };
    document.getElementById('confirm-modal').classList.add('active');
}
<?php endif; ?>
<?php if ($activeTab === 'parcours'): ?>
document.getElementById('dP').addEventListener('change', function(){ document.getElementById('fmP').submit(); });
var tP;
document.getElementById('sP').addEventListener('input', function(){
    clearTimeout(tP); tP = setTimeout(function(){ document.getElementById('fmP').submit(); }, 600);
});
function askDelP(id, nom) {
    document.getElementById('confirm-message').textContent = 'Supprimer le parcours "' + nom + '" ?';
    document.getElementById('confirm-yes').onclick = function() {
        window.location.href = 'dashboard.php?tab=parcours&del_p=' + id;
    };
    document.getElementById('confirm-modal').classList.add('active');
}
<?php endif; ?>
document.querySelectorAll('[data-modal-close]').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.getElementById('confirm-modal').classList.remove('active');
    });
});
</script>
</body>
</html>
