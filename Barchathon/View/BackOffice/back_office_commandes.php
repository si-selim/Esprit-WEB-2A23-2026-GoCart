<!DOCTYPE html>
<?php
include '../../controller/CommandeController.php';
include '../../controller/LigneCommandeController.php';
$commandeC = new CommandeController();
$ligneC = new LigneCommandeController();

if (isset($_GET['delete_commande'])) {
    $commandeC->deleteCommande((int) $_GET['delete_commande']);
    header('Location: back_office_commandes.php');
    exit;
}

if (isset($_GET['delete_ligne'])) {
    $ligneC->deleteLigneCommande((int) $_GET['delete_ligne']);
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
    header('Location: back_office_commandes.php' . (!empty($redirectParams) ? '?' . implode('&', $redirectParams) : ''));
    exit;
}

$searchOrders = trim($_GET['search_orders'] ?? '');
$sortOrders = $_GET['sort_orders'] ?? 'datecommande';
$dirOrders = $_GET['dir_orders'] ?? 'DESC';
$viewCommande = $_GET['view_commande'] ?? null;
$searchLignes = trim($_GET['search_lignes'] ?? '');
$sortLignes = $_GET['sort_lignes'] ?? 'idligne';
$dirLignes = $_GET['dir_lignes'] ?? 'ASC';

$list = $commandeC->listCommandesFiltered($searchOrders, $sortOrders, $dirOrders);
$lignes = $ligneC->listLignesCommandeFiltered($viewCommande, $searchLignes, $sortLignes, $dirLignes);
?>
<!-- saved from url=(0070)http://localhost/MARATHONS/View/BackOffice/listMarathons.php#marathons -->
<html lang="fr"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office Commandes</title>
    <style>
        :root {
            --ink:#102a43;
            --teal:#0f766e;
            --sun:#ffb703;
            --bg:#f4fbfb;
            --card:#ffffff;
            --muted:#627d98;
            --coral:#e76f51;
            --line:#d9e2ec;
            --nav:#0b2032;
        }
        * { box-sizing:border-box; }
        body {
            margin:0;
            font-family:"Segoe UI",sans-serif;
            color:var(--ink);
            background:linear-gradient(180deg,#fefaf0 0%, var(--bg) 100%);
        }
        .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
        .sidebar {
            background:linear-gradient(180deg, #0b2032 0%, #12314a 100%);
            color:#fff;
            padding:28px 22px;
            position:sticky;
            top:0;
            height:100vh;
            display:flex;
            flex-direction:column;
            gap:24px;
        }
        .brand {
            display:grid;
            gap:10px;
            padding-bottom:20px;
            border-bottom:1px solid rgba(255,255,255,.14);
        }
        .brand-badge {
            width:52px;
            height:52px;
            border-radius:18px;
            display:block;
            object-fit:cover;
            background:#fff;
            border:1px solid rgba(255,255,255,.16);
        }
        .brand small, .side-note { color:rgba(255,255,255,.72); }
        .side-nav { display:grid; gap:10px; }
        .side-link {
            text-decoration:none;
            color:#fff;
            border:1px solid rgba(255,255,255,.1);
            background:rgba(255,255,255,.05);
            border-radius:16px;
            padding:12px 14px;
            font-weight:700;
        }
        .side-link.cta { background:linear-gradient(135deg,var(--teal),#14b8a6); border:0; }
        .content { padding:28px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
        .head h1 { margin:0; font-size:2.2rem; }
        .muted { color:var(--muted); line-height:1.65; }
        .actions { display:flex; flex-wrap:wrap; gap:12px; }
        .btn { text-decoration:none; padding:11px 16px; border-radius:14px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .btn-primary { background:var(--teal); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.1); }
        .btn-warning { background:var(--sun); color:var(--ink); }
        .btn-danger { background:var(--coral); color:#fff; }
        .grid { display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); margin-bottom:24px; }
        .card, .section-card {
            background:var(--card);
            border-radius:24px;
            padding:22px;
            box-shadow:0 14px 34px rgba(16,42,67,.08);
            border:1px solid rgba(16,42,67,.08);
        }
        .stat { font-size:2rem; font-weight:900; color:var(--teal); }
        .stack { display:grid; gap:18px; }
        .section-title { margin:0 0 14px; font-size:1.45rem; }
        .table-shell { overflow:auto; }
        table { width:100%; min-width:880px; border-collapse:collapse; background:#fff; }
        th, td { padding:14px 12px; text-align:left; border-bottom:1px solid #e6edf3; vertical-align:top; }
        th { background:#102a43; color:#fff; position:sticky; top:0; }
        .table-actions { display:flex; flex-wrap:wrap; gap:8px; }
        .tag { display:inline-block; padding:6px 10px; border-radius:999px; background:rgba(15,118,110,.12); color:var(--teal); font-weight:800; font-size:.86rem; }
        .mobile-nav { display:none; }
        @media (max-width: 980px) {
            .layout { grid-template-columns:1fr; }
            .sidebar { display:none; }
            .mobile-nav { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:18px; }
            .content { padding:20px; }
            .head { flex-direction:column; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-badge" src="./Back Office marathons_files/LOGO.jpg" alt="BarchaThon">
                <div>
                    <strong>Admin Back Office</strong><br>
                    <small>Administrateur - admin</small>
                </div>
            </div>

            <nav class="side-nav">
                                    <a class="side-link" href="#stats">Statistiques</a>
                    <a class="side-link" href="#users">Utilisateurs</a>
                    <a class="side-link" href="#orders">Commandes</a>
                    <a class="side-link" href="#lignes">Lignes de commandes</a>
                    <a class="side-link" href="#marathons">Marathons</a>
                    <a class="side-link" href="#routes">Parcours</a>
                    <a class="side-link" href="#stands">Stands</a>
                    <a class="side-link" href="#reclamations">Reclamations</a>
            </nav>

            <div class="side-note">
                                    Consultation des commandes et données du système.
                            </div>
        </aside>

        <main class="content">
            <div class="mobile-nav">
            </div>

            <div class="head">
                <div>
                    <h1>Administration generale</h1>
                    <div class="muted">
                                                    Vous pouvez consulter les commandes, utilisateurs et autres données.
                                            </div>
                </div>
                <div class="actions">
                                            <span class="tag">Vue admin globale</span>
                                    </div>
            </div>

                            <section class="grid" id="stats">
                                            <div class="card">
                            <div class="stat">6</div>
                            <div>Marathons actifs</div>
                        </div>
                                            <div class="card">
                            <div class="stat">3060</div>
                            <div>Places disponibles</div>
                        </div>
                                            <div class="card">
                            <div class="stat">2</div>
                            <div>Parcours</div>
                        </div>
                                            <div class="card">
                            <div class="stat">2</div>
                            <div>Stands partenaires</div>
                        </div>
                                    </section>
            
            <div class="stack">
                                    <section class="section-card" id="users">
                        <h2 class="section-title">Tous les utilisateurs</h2>
                        <div class="table-shell">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Nom utilisateur</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Pays</th>
                                        <th>Ville / zone</th>
                                        <th>Telephone</th>
                                        <th>Occupation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                                                                                                                                                    <tr>
                                                <td>Organisateur Demo</td>
                                                <td>organisateur</td>
                                                <td>organisateur</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                                                                                                                <tr>
                                                <td>Organisateur Nord</td>
                                                <td>organisateur2</td>
                                                <td>organisateur</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                                                                                                                <tr>
                                                <td>Participant Demo</td>
                                                <td>participant</td>
                                                <td>participant</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                                                                                                                <tr>
                                                <td>mokhtar ben khaled</td>
                                                <td>mokh</td>
                                                <td>participant</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                                                                                                                <tr>
                                                <td>mokhtar ben khaled</td>
                                                <td>momo</td>
                                                <td>participant</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                                                                                                                <tr>
                                                <td>Med Mokhtar</td>
                                                <td>Ben khaled</td>
                                                <td>participant</td>
                                                <td>loulou.arfaoui72@gmail.com</td>
                                                <td>Tunisie</td>
                                                <td>Ariana</td>
                                                <td>98200136</td>
                                                <td>Etudiant</td>
                                            </tr>
                                                                                                            </tbody>
                            </table>
                        </div>
                    </section>
                
                <section class="section-card" id="marathons">
                    <h2 class="section-title">Tous les marathons</h2>
                    <div class="table-shell">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Marathon</th>
                                    <th>Ville</th>
                                    <th>Date</th>
                                    <th>Distance</th>
                                    <th>Statut</th>
                                    <th>Places</th>
                                    <th>Type</th>
                                    <th>Organisateur</th>
                                    <th>Prix</th>
                                    <th>Visibilite</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                                                                                            <tr>
                                            <td>1</td>
                                            <td>Marathon de Tunis Medina</td>
                                            <td>Tunis</td>
                                            <td>2026-05-18</td>
                                            <td>42 km</td>
                                            <td>Inscriptions ouvertes</td>
                                            <td>240</td>
                                            <td>Urbain</td>
                                            <td>Run Tunisia Events</td>
                                            <td>65.00 TND</td>
                                            <td>
                                                                                                    <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?toggle_visibility=1#marathons" title="Rendre invisible">
                                                        👁                                                    </a>
                                                                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                                                                            <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/manageMarathon.php?id=1">Parcours et stands</a>
                                                        <a class="btn btn-warning" href="http://localhost/MARATHONS/View/BackOffice/updateMarathon.php?id=1">Modifier</a>
                                                                                                                                                                <a class="btn btn-danger" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?delete=1" onclick="return confirm(&#39;Supprimer ce marathon ?&#39;);">Supprimer</a>
                                                                                                    </div>
                                            </td>
                                        </tr>
                                                                            <tr>
                                            <td>2</td>
                                            <td>Semi Marathon de Sousse Corniche</td>
                                            <td>Sousse</td>
                                            <td>2026-06-08</td>
                                            <td>21 km</td>
                                            <td>Places limitees</td>
                                            <td>120</td>
                                            <td>Bord de mer</td>
                                            <td>Ocean Pace Club</td>
                                            <td>48.00 TND</td>
                                            <td>
                                                                                                    <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?toggle_visibility=2#marathons" title="Rendre invisible">
                                                        👁                                                    </a>
                                                                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                                                                            <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/manageMarathon.php?id=2">Parcours et stands</a>
                                                        <a class="btn btn-warning" href="http://localhost/MARATHONS/View/BackOffice/updateMarathon.php?id=2">Modifier</a>
                                                                                                                                                                <a class="btn btn-danger" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?delete=2" onclick="return confirm(&#39;Supprimer ce marathon ?&#39;);">Supprimer</a>
                                                                                                    </div>
                                            </td>
                                        </tr>
                                                                            <tr>
                                            <td>3</td>
                                            <td>Trail de Zaghouan</td>
                                            <td>Zaghouan</td>
                                            <td>2026-09-14</td>
                                            <td>15 km</td>
                                            <td>Nouveau</td>
                                            <td>180</td>
                                            <td>Trail</td>
                                            <td>Atlas Outdoor</td>
                                            <td>38.00 TND</td>
                                            <td>
                                                                                                    <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?toggle_visibility=3#marathons" title="Rendre invisible">
                                                        👁                                                    </a>
                                                                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                                                                            <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/manageMarathon.php?id=3">Parcours et stands</a>
                                                        <a class="btn btn-warning" href="http://localhost/MARATHONS/View/BackOffice/updateMarathon.php?id=3">Modifier</a>
                                                                                                                                                                <a class="btn btn-danger" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?delete=3" onclick="return confirm(&#39;Supprimer ce marathon ?&#39;);">Supprimer</a>
                                                                                                    </div>
                                            </td>
                                        </tr>
                                                                            <tr>
                                            <td>4</td>
                                            <td>Marathon International de Djerba</td>
                                            <td>Djerba</td>
                                            <td>2026-11-01</td>
                                            <td>42 km</td>
                                            <td>Premium</td>
                                            <td>320</td>
                                            <td>Destination</td>
                                            <td>Djerba Sport Tourism</td>
                                            <td>82.00 TND</td>
                                            <td>
                                                                                                    <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?toggle_visibility=4#marathons" title="Rendre invisible">
                                                        👁                                                    </a>
                                                                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                                                                            <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/manageMarathon.php?id=4">Parcours et stands</a>
                                                        <a class="btn btn-warning" href="http://localhost/MARATHONS/View/BackOffice/updateMarathon.php?id=4">Modifier</a>
                                                                                                                                                                <a class="btn btn-danger" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?delete=4" onclick="return confirm(&#39;Supprimer ce marathon ?&#39;);">Supprimer</a>
                                                                                                    </div>
                                            </td>
                                        </tr>
                                                                            <tr>
                                            <td>5</td>
                                            <td>jarya</td>
                                            <td>ghazella</td>
                                            <td>2026-04-03</td>
                                            <td>42 km</td>
                                            <td>Inscriptions ouvertes</td>
                                            <td>2000</td>
                                            <td>Urbain</td>
                                            <td>mokh</td>
                                            <td>0.00 TND</td>
                                            <td>
                                                                                                    <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?toggle_visibility=5#marathons" title="Rendre invisible">
                                                        👁                                                    </a>
                                                                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                                                                            <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/manageMarathon.php?id=5">Parcours et stands</a>
                                                        <a class="btn btn-warning" href="http://localhost/MARATHONS/View/BackOffice/updateMarathon.php?id=5">Modifier</a>
                                                                                                                                                                <a class="btn btn-danger" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?delete=5" onclick="return confirm(&#39;Supprimer ce marathon ?&#39;);">Supprimer</a>
                                                                                                    </div>
                                            </td>
                                        </tr>
                                                                            <tr>
                                            <td>6</td>
                                            <td>heye</td>
                                            <td>ghazella</td>
                                            <td>2026-04-17</td>
                                            <td>5 km</td>
                                            <td>Inscriptions ouvertes</td>
                                            <td>200</td>
                                            <td>Urbain</td>
                                            <td>mokh</td>
                                            <td>1.00 TND</td>
                                            <td>
                                                                                                    <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?toggle_visibility=6#marathons" title="Rendre visible">
                                                        🙈                                                    </a>
                                                                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                                                                            <a class="btn btn-secondary" href="http://localhost/MARATHONS/View/BackOffice/manageMarathon.php?id=6">Parcours et stands</a>
                                                        <a class="btn btn-warning" href="http://localhost/MARATHONS/View/BackOffice/updateMarathon.php?id=6">Modifier</a>
                                                                                                                                                                <a class="btn btn-danger" href="http://localhost/MARATHONS/View/BackOffice/listMarathons.php?delete=6" onclick="return confirm(&#39;Supprimer ce marathon ?&#39;);">Supprimer</a>
                                                                                                    </div>
                                            </td>
                                        </tr>
                                                                                                </tbody>
                        </table>
                    </div>
                </section>

                                    <section class="section-card" id="orders">
                        <h2 class="section-title">Toutes les commandes</h2>
                        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                            <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                                <input type="text" name="search_orders" value="<?php echo htmlspecialchars($searchOrders); ?>" placeholder="Rechercher commandes..." style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;" />
                                <select name="sort_orders" style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;">
                                    <option value="datecommande"<?php echo $sortOrders === 'datecommande' ? ' selected' : ''; ?>>Date</option>
                                    <option value="montanttotale"<?php echo $sortOrders === 'montanttotale' ? ' selected' : ''; ?>>Montant</option>
                                    <option value="idcommande"<?php echo $sortOrders === 'idcommande' ? ' selected' : ''; ?>>ID</option>
                                </select>
                                <select name="dir_orders" style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;">
                                    <option value="DESC"<?php echo $dirOrders === 'DESC' ? ' selected' : ''; ?>>Décroissant</option>
                                    <option value="ASC"<?php echo $dirOrders === 'ASC' ? ' selected' : ''; ?>>Croissant</option>
                                </select>
                                <?php if (!empty($viewCommande)): ?>
                                    <input type="hidden" name="view_commande" value="<?php echo htmlspecialchars($viewCommande); ?>" />
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary">Appliquer</button>
                                <a href="back_office_commandes.php" class="btn btn-secondary">Réinitialiser</a>
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
                                    <?php
                                    foreach ($list as $commande) {
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
                                        echo '<tr>';
                                        echo '<td>#' . $commande['idcommande'] . '</td>';
                                        echo '<td>Utilisateur ' . $commande['idutilisateur'] . '</td>';
                                        echo '<td>Stand ' . $commande['idstand'] . '</td>';
                                        echo '<td>' . date('d/m/Y H:i', strtotime($commande['datecommande'])) . '</td>';
                                        echo '<td>' . number_format($commande['montanttotale'], 2, ',', ' ') . ' €</td>';
                                        echo '<td>OUI</td>';
                                        echo '<td>' . $statut_label . '</td>';
                                        echo '<td>';
                                        echo '<a href="?view_commande=' . $commande['idcommande'] . '&search_orders=' . urlencode($searchOrders) . '&sort_orders=' . urlencode($sortOrders) . '&dir_orders=' . urlencode($dirOrders) . '" class="btn btn-secondary" style="margin-right:8px;">Voir</a>';
                                        echo '<a href="?delete_commande=' . $commande['idcommande'] . '" class="btn btn-danger" onclick="return confirm(\'Supprimer cette commande ?\');">Supprimer</a>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="section-card" id="lignes">
                        <h2 class="section-title">Toutes les lignes de commandes</h2>
                        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                            <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                                <input type="text" name="search_lignes" value="<?php echo htmlspecialchars($searchLignes); ?>" placeholder="Rechercher lignes..." style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;" />
                                <select name="sort_lignes" style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;">
                                    <option value="idligne"<?php echo $sortLignes === 'idligne' ? ' selected' : ''; ?>>ID Ligne</option>
                                    <option value="idcommande"<?php echo $sortLignes === 'idcommande' ? ' selected' : ''; ?>>ID Commande</option>
                                    <option value="quantite"<?php echo $sortLignes === 'quantite' ? ' selected' : ''; ?>>Quantité</option>
                                    <option value="prixunitaire"<?php echo $sortLignes === 'prixunitaire' ? ' selected' : ''; ?>>Prix</option>
                                </select>
                                <select name="dir_lignes" style="padding:10px 12px; border-radius:10px; border:1px solid #ccc;">
                                    <option value="ASC"<?php echo $dirLignes === 'ASC' ? ' selected' : ''; ?>>Croissant</option>
                                    <option value="DESC"<?php echo $dirLignes === 'DESC' ? ' selected' : ''; ?>>Décroissant</option>
                                </select>
                                <?php if (!empty($viewCommande)): ?>
                                    <input type="hidden" name="view_commande" value="<?php echo htmlspecialchars($viewCommande); ?>" />
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary">Appliquer</button>
                                <a href="back_office_commandes.php<?php echo !empty($viewCommande) ? '?view_commande=' . urlencode($viewCommande) : ''; ?>" class="btn btn-secondary">Réinitialiser</a>
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
                                        <th>Prix Unitaire</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($lignes as $ligne) {
                                        echo '<tr>';
                                        echo '<td>' . $ligne['idligne'] . '</td>';
                                        echo '<td>' . $ligne['idcommande'] . '</td>';
                                        echo '<td>' . $ligne['idproduit'] . '</td>';
                                        echo '<td>' . $ligne['quantite'] . '</td>';
                                        echo '<td>' . number_format($ligne['prixunitaire'], 2, ',', ' ') . ' €</td>';
                                        echo '<td>';
                                        echo '<a href="?delete_ligne=' . $ligne['idligne'] . '&view_commande=' . urlencode($viewCommande) . '&search_lignes=' . urlencode($searchLignes) . '&sort_lignes=' . urlencode($sortLignes) . '&dir_lignes=' . urlencode($dirLignes) . '" class="btn btn-danger" onclick="return confirm(\'Supprimer cette ligne de commande ?\');">Supprimer</a>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="section-card" id="routes">
                        <h2 class="section-title">Tous les parcours</h2>
                        <div class="table-shell">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Marathon</th>
                                        <th>ID parcours</th>
                                        <th>Nom</th>
                                        <th>Distance</th>
                                        <th>Difficulte</th>
                                        <th>Zones traversees</th>
                                    </tr>
                                </thead>
                                <tbody>
                                                                                                                        <tr>
                                                <td>jarya</td>
                                                <td>#1</td>
                                                <td>aa</td>
                                                <td>9.83 km</td>
                                                <td>Facile</td>
                                                <td>Zone nord</td>
                                            </tr>
                                                                                    <tr>
                                                <td>heye</td>
                                                <td>#1</td>
                                                <td>jarya khafefi</td>
                                                <td>29.49 km</td>
                                                <td>Moyen</td>
                                                <td>borj touil</td>
                                            </tr>
                                                                                                            </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="section-card" id="stands">
                        <h2 class="section-title">Tous les stands</h2>
                        <div class="table-shell">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Marathon</th>
                                        <th>ID stand</th>
                                        <th>Nom</th>
                                        <th>Categorie</th>
                                        <th>Description</th>
                                        <th>Produits disponibles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                                                                                                        <tr>
                                                <td>jarya</td>
                                                <td>#1</td>
                                                <td>stand 1</td>
                                                <td>boire</td>
                                                <td>oui</td>
                                                <td>3</td>
                                            </tr>
                                                                                    <tr>
                                                <td>heye</td>
                                                <td>#1</td>
                                                <td>stand 1</td>
                                                <td>aa</td>
                                                <td>aa</td>
                                                <td>4</td>
                                            </tr>
                                                                                                            </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="section-card" id="reclamations">
                        <h2 class="section-title">Reclamations</h2>
                        <div class="table-shell">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Commande</th>
                                        <th>Marathon</th>
                                        <th>Stand</th>
                                        <th>Participant</th>
                                        <th>Status</th>
                                        <th>Messages</th>
                                        <th>Dernier message</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                                                                                                        <tr>
                                                <td>#1</td>
                                                <td>jarya</td>
                                                <td>stand 1</td>
                                                <td>mokh</td>
                                                <td>validé</td>
                                                <td>6</td>
                                                <td>
                                                    <strong>Organisateur Demo</strong><br>
                                                    aa                                                </td>
                                                <td>02/04/2026 13:18</td>
                                            </tr>
                                                                                                            </tbody>
                            </table>
                        </div>
                    </section>
                            </div>
        </main>
    </div>


</body></html>